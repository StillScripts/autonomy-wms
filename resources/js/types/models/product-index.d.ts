export interface ProductIndex {
    current_page: number;
    data: Data[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: Link[];
    next_page_url: null;
    path: string;
    per_page: number;
    prev_page_url: null;
    to: number;
    total: number;
}

interface Data {
    id: number;
    organisation_id: number;
    name: string;
    description: string;
    price: string;
    currency: string;
    active: boolean;
    provider_type: string;
    provider_product_id: string;
    metadata: Record<string, string>[];
    created_at: Date;
    updated_at: Date;
    stripe_product: StripeProduct;
    product_types?: Array<{
        id: number;
        name: string;
        slug: string;
    }>;
}

interface StripeProduct {
    id: number;
    product_id: number;
    stripe_id: string;
    stripe_price_id: string;
    stripe_environment: string;
    stripe_metadata: Record<string, string>[];
    created_at: Date;
    updated_at: Date;
}

interface Link {
    url: null | string;
    label: string;
    active: boolean;
}
