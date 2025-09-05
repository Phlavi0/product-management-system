<template>
  <q-dialog 
    v-model="showDialog" 
    persistent 
    max-width="500px"
  >
    <q-card style="min-width: 400px">
      <q-card-section>
        <div class="text-h6">
          {{ isEdit ? 'Edit Product' : 'Create Product' }}
        </div>
      </q-card-section>

      <q-card-section>
        <q-form @submit="handleSubmit" class="q-gutter-md">
          <q-input
            v-model="form.product_name"
            label="Product Name *"
            required
            :rules="[val => !!val || 'Product name is required']"
            outlined
          />

          <q-select
            v-model="form.product_type"
            :options="productTypeOptions"
            label="Product Type *"
            required
            :rules="[val => !!val || 'Product type is required']"
            outlined
            emit-value
            map-options
          />

          <q-select
            v-model="form.product_parent_id"
            :options="parentOptions"
            label="Parent Product"
            outlined
            clearable
            emit-value
            map-options
            option-value="value"
            option-label="label"
          />

          <div class="row justify-end q-gutter-sm">
            <q-btn 
              label="Cancel" 
              color="grey" 
              flat 
              @click="closeDialog"
            />
            <q-btn 
              label="Save" 
              color="primary" 
              type="submit"
              :loading="loading"
            />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue';
import type { Product, ProductFormData } from '../types/product';
import { useProductStore } from '../stores/productStore';

// Props & Emits
interface Props {
  modelValue: boolean;
  product?: Product | null;
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void;
  (e: 'saved', product: Product): void;
}

const props = withDefaults(defineProps<Props>(), {
  product: null
});

const emit = defineEmits<Emits>();

// Store
const productStore = useProductStore();

// State
const form = reactive<ProductFormData>({
  product_name: '',
  product_type: '',
  product_parent_id: null
});

const loading = ref(false);

// Computed
const showDialog = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const isEdit = computed(() => !!props.product);

const productTypeOptions = [
  { label: 'Category', value: 'category' },
  { label: 'Subcategory', value: 'subcategory' },
  { label: 'Product', value: 'product' },
  { label: 'Item', value: 'item' }
];

const parentOptions = computed(() => {
  const options = [{ label: 'No Parent (Root)', value: null as number | null }];
  
  productStore.products.forEach(product => {
    if (!props.product || product.product_id !== props.product.product_id) {
      options.push({
        label: `${product.product_name} (${product.product_type})`,
        value: product.product_id
      });
    }
  });
  
  return options;
});

// Methods
const resetForm = () => {
  form.product_name = '';
  form.product_type = '';
  form.product_parent_id = null;
};

const populateForm = (product: Product) => {
  form.product_name = product.product_name;
  form.product_type = product.product_type;
  form.product_parent_id = product.product_parent_id;
};

const closeDialog = () => {
  showDialog.value = false;
  setTimeout(() => {
    resetForm();
  }, 300);
};

const handleSubmit = async () => {
  loading.value = true;
  
  try {
    let savedProduct: Product;
    
    if (isEdit.value && props.product) {
      savedProduct = await productStore.updateProduct(props.product.product_id, form);
    } else {
      savedProduct = await productStore.createProduct(form);
    }
    
    emit('saved', savedProduct);
    closeDialog();
  } catch (error) {
    console.error('Error saving product:', error);
  } finally {
    loading.value = false;
  }
};

// Watchers
watch(() => props.product, (newProduct) => {
  if (newProduct) {
    populateForm(newProduct);
  } else {
    resetForm();
  }
}, { immediate: true });

watch(() => props.modelValue, (newValue) => {
  if (newValue && productStore.products.length === 0) {
    void productStore.fetchProducts();
  }
});
</script>