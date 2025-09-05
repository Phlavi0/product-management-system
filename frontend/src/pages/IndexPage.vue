<template>
  <q-page class="row items-start q-pa-md">
    <div class="full-width">
      <!-- Header -->
      <div class="row items-center q-mb-lg">
        <div class="col">
          <h4 class="q-ma-none">Product Management System</h4>
          <p class="text-grey-7 q-ma-none">
            Manage your product hierarchy with ease
          </p>
        </div>
        <div class="col-auto">
          <q-btn-toggle
            v-model="currentView"
            :options="viewOptions"
            color="primary"
            outline
          />
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="row q-gutter-md q-mb-lg">
        <div class="col-12 col-sm-6 col-md-3">
          <q-card class="text-center">
            <q-card-section>
              <q-icon name="inventory_2" size="2em" color="primary" />
              <div class="text-h5 q-mt-sm">{{ totalProducts }}</div>
              <div class="text-grey-7">Total Products</div>
            </q-card-section>
          </q-card>
        </div>
        
        <div class="col-12 col-sm-6 col-md-3">
          <q-card class="text-center">
            <q-card-section>
              <q-icon name="folder" size="2em" color="blue" />
              <div class="text-h5 q-mt-sm">{{ categoryCount }}</div>
              <div class="text-grey-7">Categories</div>
            </q-card-section>
          </q-card>
        </div>
        
        <div class="col-12 col-sm-6 col-md-3">
          <q-card class="text-center">
            <q-card-section>
              <q-icon name="folder_open" size="2em" color="green" />
              <div class="text-h5 q-mt-sm">{{ subcategoryCount }}</div>
              <div class="text-grey-7">Subcategories</div>
            </q-card-section>
          </q-card>
        </div>
        
        <div class="col-12 col-sm-6 col-md-3">
          <q-card class="text-center">
            <q-card-section>
              <q-icon name="article" size="2em" color="orange" />
              <div class="text-h5 q-mt-sm">{{ productCount }}</div>
              <div class="text-grey-7">Items</div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Main Content -->
      <transition name="fade" mode="out-in">
        <ProductList v-if="currentView === 'list'" key="list" />
        <ProductTree v-else-if="currentView === 'tree'" key="tree" />
      </transition>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useProductStore } from '../stores/productStore';
import ProductList from '../components/ProductList.vue';
import ProductTree from '../components/ProductTree.vue';

// Store
const productStore = useProductStore();

// State
const currentView = ref('tree');

const viewOptions = [
  { label: 'Tree View', value: 'tree', icon: 'account_tree' },
  { label: 'List View', value: 'list', icon: 'list' }
];

// Computed
const totalProducts = computed(() => productStore.products.length);

const categoryCount = computed(() => 
  productStore.products.filter(p => p.product_type === 'category').length
);

const subcategoryCount = computed(() => 
  productStore.products.filter(p => p.product_type === 'subcategory').length
);

const productCount = computed(() => 
  productStore.products.filter(p => p.product_type === 'product').length
);

// Lifecycle
onMounted(() => {
  void productStore.fetchProducts();
  void productStore.fetchProductTree();
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>