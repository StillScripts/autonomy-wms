export interface Product {
    id: number;
    organisation_id: number;
    name: string;
    description: string;
    price: number;
    currency: string;
    active: boolean;
    provider_type: string;
    provider_product_id: string;
    metadata: Record<string, unknown>;
    created_at: string;
    updated_at: string;
    private_files?: Array<{
        id: number;
        name: string;
        description: string | null;
        content_type: string;
        file_size: string;
        pivot: {
            sort_order: number;
            metadata: Record<string, unknown>;
        };
    }>;
} 