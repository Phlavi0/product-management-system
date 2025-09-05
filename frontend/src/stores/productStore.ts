import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { Product, ProductFormData, ProductFilters, TreeNode } from '../types/product';
import { productApi } from '../services/productApi';
import { Notify } from 'quasar';

export const useProductStore = defineStore('product', () => {
  // State
  const products = ref<Product[]>([]);
  const productTree = ref<Product[]>([]);
  const currentProduct = ref<Product | null>(null);
  const loading = ref(false);
  const filters = ref<ProductFilters>({});

  // Getters
  const rootProducts = computed(() => 
    products.value.filter(p => p.product_parent_id === null)
  );

  const productTypes = computed(() => {
    const types = new Set(products.value.map(p => p.product_type));
    return Array.from(types);
  });

  const getProductById = computed(() => (id: number) => 
    products.value.find(p => p.product_id === id)
  );

  const getChildProducts = computed(() => (parentId: number) =>
    products.value.filter(p => p.product_parent_id === parentId)
  );

  const treeNodes = computed((): TreeNode[] => {
    return convertToTreeNodes(productTree.value);
  });

  // Helper function to convert products to tree nodes
  const convertToTreeNodes = (products: Product[]): TreeNode[] => {
    return products.map(product => {
        const children = product.descendants ? convertToTreeNodes(product.descendants) : [];
        
        return {
        id: product.product_id,
        label: product.product_name,
        icon: getProductIcon(product.product_type),
        product: product,
        children: children
        };
    });
    };

  const getProductIcon = (type: string): string => {
    const iconMap: Record<string, string> = {
      'category': 'folder',
      'subcategory': 'folder_open',
      'product': 'inventory_2',
      'item': 'article'
    };
    return iconMap[type] || 'circle';
  };

  // Actions
  const fetchProducts = async (newFilters?: ProductFilters) => {
    loading.value = true;
    try {
      if (newFilters) {
        filters.value = { ...filters.value, ...newFilters };
      }
      const response = await productApi.getProducts(filters.value);
      products.value = response.data;
    } catch (error) {
      console.error('Failed to fetch products:', error);
      Notify.create({
        type: 'negative',
        message: 'Failed to fetch products',
        position: 'top'
      });
    } finally {
      loading.value = false;
    }
  };

  const fetchProductTree = async () => {
    loading.value = true;
    try {
      const response = await productApi.getProductTree();
      productTree.value = response.data;
    } catch (error) {
      console.error('Failed to fetch product tree:', error);
      Notify.create({
        type: 'negative',
        message: 'Failed to fetch product tree',
        position: 'top'
      });
    } finally {
      loading.value = false;
    }
  };

  const fetchProduct = async (id: number) => {
    loading.value = true;
    try {
      const response = await productApi.getProduct(id);
      currentProduct.value = response.data;
      return response.data;
    } catch (error) {
      console.error('Failed to fetch product:', error);
      Notify.create({
        type: 'negative',
        message: 'Failed to fetch product',
        position: 'top'
      });
      return null;
    } finally {
      loading.value = false;
    }
  };

  const createProduct = async (data: ProductFormData) => {
    loading.value = true;
    try {
      const response = await productApi.createProduct(data);
      products.value.push(response.data);
      
      Notify.create({
        type: 'positive',
        message: response.message || 'Product created successfully',
        position: 'top'
      });
      
      // Refresh tree if needed
      if (!data.product_parent_id) {
        await fetchProductTree();
      }
      
      return response.data;
  } catch (error: unknown) {
      console.error('Failed to create product:', error);
      let message = 'Failed to create product';
      if (
        typeof error === 'object' &&
        error !== null &&
        'response' in error &&
        typeof (error as { response?: { data?: { message?: string } } }).response?.data?.message === 'string'
      ) {
        message = (error as { response: { data: { message: string } } }).response.data.message;
      }
      
      Notify.create({
        type: 'negative',
        message,
        position: 'top'
      });
      
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const updateProduct = async (id: number, data: ProductFormData) => {
    loading.value = true;
    try {
      const response = await productApi.updateProduct(id, data);
      const index = products.value.findIndex(p => p.product_id === id);
      if (index !== -1) {
        products.value[index] = response.data;
      }
      
      Notify.create({
        type: 'positive',
        message: response.message || 'Product updated successfully',
        position: 'top'
      });
      
      // Refresh tree
      await fetchProductTree();
      
      return response.data;
  } catch (error: unknown) {
      console.error('Failed to update product:', error);
      let message = 'Failed to update product';
      if (
        typeof error === 'object' &&
        error !== null &&
        'response' in error &&
        typeof (error as { response?: { data?: { message?: string } } }).response?.data?.message === 'string'
      ) {
        message = (error as { response: { data: { message: string } } }).response.data.message;
      }
      
      Notify.create({
        type: 'negative',
        message,
        position: 'top'
      });
      
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const deleteProduct = async (id: number) => {
    loading.value = true;
    try {
      const response = await productApi.deleteProduct(id);
      products.value = products.value.filter(p => p.product_id !== id);
      
      Notify.create({
        type: 'positive',
        message: response.message || 'Product deleted successfully',
        position: 'top'
      });
      
      // Refresh tree
      await fetchProductTree();
      
  } catch (error: unknown) {
      console.error('Failed to delete product:', error);
      let message = 'Failed to delete product';
      if (
        typeof error === 'object' &&
        error !== null &&
        'response' in error &&
        typeof (error as { response?: { data?: { message?: string } } }).response?.data?.message === 'string'
      ) {
        message = (error as { response: { data: { message: string } } }).response.data.message;
      }
      
      Notify.create({
        type: 'negative',
        message,
        position: 'top'
      });
      
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const clearFilters = () => {
    filters.value = {};
  };

  return {
    // State
    products,
    productTree,
    currentProduct,
    loading,
    filters,
    
    // Getters
    rootProducts,
    productTypes,
    getProductById,
    getChildProducts,
    treeNodes,
    
    // Actions
    fetchProducts,
    fetchProductTree,
    fetchProduct,
    createProduct,
    updateProduct,
    deleteProduct,
    clearFilters
  };
});