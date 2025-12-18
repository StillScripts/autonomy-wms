import type { ProductIndex } from '@/types/models/product-index';

export const isTest = (product: ProductIndex['data'][number]) => {
    return product.stripe_product.stripe_environment === 'test';
};
