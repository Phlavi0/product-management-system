<template>
  <div class="product-list">
    <!-- Filters -->
    <q-card flat class="q-mb-md">
      <q-card-section>
        <div class="row q-gutter-md items-end">
          <q-input
            v-model="localFilters.search"
            label="Search products"
            outlined
            dense
            clearable
            style="min-width: 200px"
            @update:model-value="debouncedSearch"
          >
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>

          <q-select
            v-model="localFilters.type"
            :options="typeOptions"
            label="Product Type"
            outlined
            dense
            clearable
            style="min-width: 150px"
            @update:model-value="applyFilters"
          />

          <q-select
            v-model="localFilters.parent_id"
            :options="parentFilterOptions"
            label="Parent Product"
            outlined
            dense
            clearable
            style="min-width: 200px"
            emit-value
            map-options
            @update:model-value="applyFilters"
          />

          <q-btn
            color="secondary"
            icon="clear"
            label="Clear Filters"
            @click="clearAllFilters"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- Products Table -->
    <q-table
      :rows="products"
      :columns="columns"
      :loading="loading"
      row-key="product_id"
      :pagination="{ rowsPerPage: 10 }"
      class="product-table"
    >
      <template v-slot:top>
        <div class="row full-width justify-between items-center">
          <div class="text-h6">Products</div>
          <q-btn
            color="primary"
            icon="add"
            label="Add Product"
            @click="showCreateDialog = true"
          />
        </div>
      </template>

      <template v-slot:body-cell-product_type="props">
        <q-td :props="props">
          <q-chip
            :color="getTypeColor(props.value)"
            text-color="white"
            :label="props.value"
            size="sm"
          />
        </q-td>
      </template>

      <template v-slot:body-cell-parent="props">
        <q-td :props="props">
          <span v-if="props.row.parent">
            {{ props.row.parent.product_name }}
          </span>
          <q-chip v-else label="Root" color="grey-5" size="sm" />
        </q-td>
      </template>

      <template v-slot:body-cell-children_count="props">
        <q-td :props="props">
          <q-badge 
            :color="props.value > 0 ? 'positive' : 'grey-5'" 
            :label="props.value"
          />
        </q-td>
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props">
          <div class="q-gutter-sm">
            <q-btn
              size="sm"
              color="primary"
              icon="edit"
              round
              dense
              @click="editProduct(props.row)"
            >
              <q-tooltip>Edit Product</q-tooltip>
            </q-btn>
            
            <q-btn
              size="sm"
              color="secondary"
              icon="visibility"
              round
              dense
              @click="viewChildren(props.row)"
            >
              <q-tooltip>View Children</q-tooltip>
            </q-btn>
            
            <q-btn
              size="sm"
              color="negative"
              icon="delete"
              round
              dense
              @click="confirmDelete(props.row)"
              :disable="props.row.children && props.row.children.length > 0"
            >
              <q-tooltip>
                {{ props.row.children?.length > 0 ? 'Cannot delete product with children' : 'Delete Product' }}
              </q-tooltip>
            </q-btn>
          </div>
        </q-td>
      </template>
    </q-table>

    <!-- Create/Edit Dialog -->
    <ProductForm
      v-model="showCreateDialog"
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
          Are you sure you want to delete "{{ productToDelete?.product_name }}"?
          This action cannot be undone.
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="grey" v-close-popup />
          <q-btn 
            flat 
            label="Delete" 
            color="negative" 
            @click="handleDelete"
            :loading="loading"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { debounce } from 'quasar';
import type { QTableProps } from 'quasar';
import type { Product, ProductFilters } from '../types/product';
import { useProductStore } from '../stores/productStore';
import ProductForm from './ProductForm.vue';

// Store
const productStore = useProductStore();

// State
const localFilters = ref<ProductFilters>({});
const showCreateDialog = ref(false);
const showDeleteDialog = ref(false);
const selectedProduct = ref<Product | null>(null);
const productToDelete = ref<Product | null>(null);

// Computed
const products = computed(() => productStore.products);
const loading = computed(() => productStore.loading);

const columns: QTableProps['columns'] = [
  {
    name: 'product_name',
    label: 'Name',
    align: 'left',
    field: 'product_name',
    sortable: true
  },
  {
    name: 'product_type',
    label: 'Type',
    align: 'center',
    field: 'product_type',
    sortable: true
  },
  {
    name: 'parent',
    label: 'Parent',
    align: 'left',
    field: 'parent'
  },
  {
    name: 'children_count',
    label: 'Children',
    align: 'center',
    field: (row: Product) => row.children?.length || 0
  },
  {
    name: 'actions',
    label: 'Actions',
    align: 'center',
    field: ''
  }
];

const typeOptions = computed(() => {
  return productStore.productTypes.map(type => ({
    label: type.charAt(0).toUpperCase() + type.slice(1),
    value: type
  }));
});

const parentFilterOptions = computed(() => {
  const options = [{ label: 'Root Products Only', value: null as number | null }];
  
  productStore.rootProducts.forEach(product => {
    options.push({
      label: product.product_name,
      value: product.product_id
    });
  });
  
  return options;
});

// Methods
const getTypeColor = (type: string): string => {
  const colorMap: Record<string, string> = {
    'category': 'blue',
    'subcategory': 'green',
    'product': 'orange',
    'item': 'purple'
  };
  return colorMap[type] || 'grey';
};

const applyFilters = () => {
  void productStore.fetchProducts(localFilters.value);
};

const debouncedSearch = debounce(() => {
  applyFilters();
}, 500);

const clearAllFilters = () => {
  localFilters.value = {};
  productStore.clearFilters();
  void productStore.fetchProducts();
};

const editProduct = (product: Product) => {
  selectedProduct.value = product;
  showCreateDialog.value = true;
};

const viewChildren = (product: Product) => {
  localFilters.value = { parent_id: product.product_id };
  applyFilters();
};

const confirmDelete = (product: Product) => {
  productToDelete.value = product;
  showDeleteDialog.value = true;
};

const handleDelete = async () => {
  if (productToDelete.value) {
    try {
      await productStore.deleteProduct(productToDelete.value.product_id);
      showDeleteDialog.value = false;
      productToDelete.value = null;
    } catch (error) {
      console.error('Error deleting product:', error);
    }
  }
};

const handleProductSaved = () => {
  selectedProduct.value = null;
  void productStore.fetchProducts(localFilters.value);
};

// Lifecycle
onMounted(() => {
  void productStore.fetchProducts();
});
</script>

<style scoped>
.product-list {
  padding: 16px;
}

.product-table {
  margin-top: 16px;
}
</style>