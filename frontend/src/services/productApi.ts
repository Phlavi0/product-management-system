import { api } from 'boot/axios';
import type { Product, ProductFormData, ApiResponse, ProductFilters } from '../types/product';

export const productApi = {
  // Get all products with optional filtering
  async getProducts(filters?: ProductFilters): Promise<ApiResponse<Product[]>> {
    const params = new URLSearchParams();
    
    if (filters?.search) params.append('search', filters.search);
    if (filters?.type) params.append('type', filters.type);
    if (filters?.parent_id !== undefined && filters.parent_id !== null) {
      params.append('parent_id', filters.parent_id.toString());
    }
    if (filters?.roots_only) params.append('roots_only', 'true');

    const response = await api.get(`/products?${params.toString()}`);
    return response.data;
  },

  // Get product tree structure
  async getProductTree(): Promise<ApiResponse<Product[]>> {
    const response = await api.get('/products-tree');
    return response.data;
  },

  // Get specific product
  async getProduct(id: number): Promise<ApiResponse<Product>> {
    const response = await api.get(`/products/${id}`);
    return response.data;
  },

  // Get children of a product
  async getProductChildren(id: number): Promise<ApiResponse<Product[]>> {
    const response = await api.get(`/products/${id}/children`);
    return response.data;
  },

  // Create new product
  async createProduct(data: ProductFormData): Promise<ApiResponse<Product>> {
    const response = await api.post('/products', data);
    return response.data;
  },

  // Update existing product
  async updateProduct(id: number, data: ProductFormData): Promise<ApiResponse<Product>> {
    const response = await api.put(`/products/${id}`, data);
    return response.data;
  },

  // Delete product
  async deleteProduct(id: number): Promise<ApiResponse<void>> {
    const response = await api.delete(`/products/${id}`);
    return response.data;
  },
};