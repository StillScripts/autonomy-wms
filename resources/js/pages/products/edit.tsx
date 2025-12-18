import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import ProductForm from '@/pages/products/product-form';
import type { Product } from '@/types/models/product';
import { Head } from '@inertiajs/react';

interface Props {
    product: Product;
    availablePrivateFiles: Array<{
        id: number;
        name: string;
        description: string | null;
        content_type: string;
        file_size: string;
    }>;
}

interface PrivateFileWithPivot {
    id: number;
    name: string;
    description: string | null;
    content_type: string;
    file_size: string;
    pivot: {
        sort_order: number;
        metadata: Record<string, unknown>;
    };
}

export default function Edit({ product, availablePrivateFiles }: Props) {
    console.log(product);
    return (
        <AppLayout>
            <Head title="Edit Product Files" />
            <PageContainer backUrl={route('products.index')} heading="Edit Product Files" subheading={`Manage private files for ${product.name}`}>
                <ProductForm
                    mode="edit"
                    productId={product.id}
                    defaultValues={{
                        name: product.name,
                        description: product.description,
                        price: product.price,
                        currency: product.currency,
                        active: product.active,
                        private_files:
                            product.private_files?.map((file: PrivateFileWithPivot) => ({
                                id: file.id,
                                sort_order: file.pivot.sort_order,
                            })) || [],
                    }}
                    availablePrivateFiles={availablePrivateFiles}
                />
            </PageContainer>
        </AppLayout>
    );
}
