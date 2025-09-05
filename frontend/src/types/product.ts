export interface Product {
  product_id: number;
  product_name: string;
  product_type: string;
  product_parent_id: number | null;
  created_at?: string;
  updated_at?: string;
  parent?: Product | null;
  children?: Product[];
  descendants?: Product[];
}

export interface ProductFormData {
  product_name: string;
  product_type: string;
  product_parent_id: number | null;
}

export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
}

export interface ProductFilters {
  search?: string;
  type?: string;
  parent_id?: number | null;
  roots_only?: boolean;
}

export interface TreeNode {
  id: number;
  label: string;
  icon?: string;
  children?: TreeNode[];
  product?: Product;
}