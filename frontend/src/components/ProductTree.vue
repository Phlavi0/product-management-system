<template>
  <div class="product-tree">
    <q-card>
      <q-card-section>
        <div class="row justify-between items-center">
          <div class="text-h6">Product Hierarchy</div>
          <div class="q-gutter-sm">
            <q-btn
              color="primary"
              icon="refresh"
              label="Refresh"
              @click="refreshTree"
              :loading="loading"
            />
            <q-btn
              color="secondary"
              icon="add"
              label="Add Root Category"
              @click="addRootCategory"
            />
          </div>
        </div>
      </q-card-section>

      <q-card-section>
        <q-tree
          :nodes="treeNodes"
          node-key="id"
          :expanded="expanded"
          @update:expanded="onExpanded"
          no-connectors
        >
          <template v-slot:default-header="prop">
            <div class="row items-center full-width">
              <q-icon
                :name="prop.node.icon"
                :color="getNodeColor(prop.node.product?.product_type)"
                class="q-mr-sm"
              />
              
              <div class="col">
                <div class="text-weight-bold">
                  {{ prop.node.label }}
                </div>
                <div class="text-caption text-grey">
                  {{ prop.node.product?.product_type }}
                  <span v-if="prop.node.children?.length">
                    • {{ prop.node.children.length }} children
                  </span>
                </div>
              </div>

              <div class="q-gutter-xs">
                <q-btn
                  size="sm"
                  round
                  dense
                  icon="add"
                  color="positive"
                  @click.stop="addChild(prop.node.product)"
                >
                  <q-tooltip>Add Child</q-tooltip>
                </q-btn>
                
                <q-btn
                  size="sm"
                  round
                  dense
                  icon="edit"
                  color="primary"
                  @click.stop="editNode(prop.node.product)"
                >
                  <q-tooltip>Edit</q-tooltip>
                </q-btn>
                
                <q-btn
                  size="sm"
                  round
                  dense
                  icon="delete"
                  color="negative"
                  @click.stop="deleteNode(prop.node.product)"
                  :disable="prop.node.children && prop.node.children.length > 0"
                >
                  <q-tooltip>
                    {{ prop.node.children?.length > 0 ? 'Cannot delete node with children' : 'Delete' }}
                  </q-tooltip>
                </q-btn>
              </div>
            </div>
          </template>

          <template v-slot:default-body="prop">
            <div class="q-ml-md q-mt-sm text-grey-7">
              <div v-if="prop.node.product?.created_at">
                Created: {{ formatDate(prop.node.product.created_at) }}
              </div>
            </div>
          </template>
        </q-tree>

        <div v-if="treeNodes.length === 0 && !loading" class="text-center q-pa-lg">
          <q-icon name="inventory_2" size="4em" color="grey-5" />
          <div class="text-h6 text-grey-5 q-mt-md">No products found</div>
          <div class="text-grey-7">Start by creating your first product category</div>
          <q-btn
            color="primary"
            icon="add"
            label="Create Category"
            class="q-mt-md"
            @click="addRootCategory"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- Product Form Dialog -->
    <ProductForm
      v-model="showProductDialog"
      :product="selectedProduct"
      @saved="handleProductSaved"
    />

    <!-- Delete Confirmation -->
    <q-dialog v-model="showDeleteDialog" persistent>
      <q-card>
        <q-card-section>
          <div class="text-h6">Confirm Delete</div>
        </q-card-section>

        <q-card-section>
          <div class="q-mb-md">
            Are you sure you want to delete "{{ productToDelete?.product_name }}"?
          </div>
          <q-banner class="bg-warning text-dark" v-if="productToDelete">
            <template v-slot:avatar>
              <q-icon name="warning" />
            </template>
            This will permanently delete the product. This action cannot be undone.
          </q-banner>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="grey" v-close-popup />
          <q-btn 
            flat 
            label="Delete" 
            color="negative" 
            @click="handleDelete"
            :loading="deleteLoading"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { date } from 'quasar';
import type { Product } from '../types/product';
import { useProductStore } from '../stores/productStore';
import ProductForm from './ProductForm.vue';

// Store
const productStore = useProductStore();

// State
const expanded = ref<number[]>([]);
const showProductDialog = ref(false);
const showDeleteDialog = ref(false);
const selectedProduct = ref<Product | null>(null);
const productToDelete = ref<Product | null>(null);
const deleteLoading = ref(false);
const parentForNewProduct = ref<Product | null>(null);

// Computed
const treeNodes = computed(() => productStore.treeNodes);
const loading = computed(() => productStore.loading);

// Methods
const getNodeColor = (type?: string): string => {
  const colorMap: Record<string, string> = {
    'category': 'blue',
    'subcategory': 'green',
    'product': 'orange',
    'item': 'purple'
  };
  return colorMap[type || ''] || 'grey';
};

const formatDate = (dateString: string): string => {
  return date.formatDate(dateString, 'MMM DD, YYYY');
};

const refreshTree = async () => {
  await productStore.fetchProductTree();
};

const addRootCategory = () => {
  selectedProduct.value = null;
  parentForNewProduct.value = null;
  showProductDialog.value = true;
};

const addChild = (parent: Product | undefined) => {
  selectedProduct.value = null;
  parentForNewProduct.value = parent || null;
  showProductDialog.value = true;
};

const editNode = (product: Product | undefined) => {
  if (product) {
    selectedProduct.value = product;
    parentForNewProduct.value = null;
    showProductDialog.value = true;
  }
};

const deleteNode = (product: Product | undefined) => {
  if (product) {
    productToDelete.value = product;
    showDeleteDialog.value = true;
  }
};

const handleDelete = async () => {
  if (productToDelete.value) {
    deleteLoading.value = true;
    try {
      await productStore.deleteProduct(productToDelete.value.product_id);
      showDeleteDialog.value = false;
      productToDelete.value = null;
      await refreshTree();
    } catch (error) {
      console.error('Error deleting product:', error);
    } finally {
      deleteLoading.value = false;
    }
  }
};

const handleProductSaved = async () => {
  selectedProduct.value = null;
  parentForNewProduct.value = null;
  await refreshTree();
};

const onExpanded = (expandedKeys: readonly number[]) => {
  expanded.value = [...expandedKeys];
};

// Lifecycle
onMounted(async () => {
  await productStore.fetchProductTree();
  // Auto-expand first level
  if (treeNodes.value.length > 0) {
    expanded.value = treeNodes.value.map(node => node.id);
  }
});
</script>

<style scoped>
.product-tree {
  padding: 16px;
}

:deep(.q-tree__node-header) {
  padding: 8px;
}

:deep(.q-tree__node-body) {
  padding: 4px 8px;
}
</style>