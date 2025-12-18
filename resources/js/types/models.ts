export interface Website {
    id: number;
    title: string;
    domain: string;
    description?: string;
    logo?: string;
    logo_url?: string;
    organisation_id: number;
    created_at: string;
    updated_at: string;
}

export interface Organisation {
    id: number;
    name: string;
    is_super_org: boolean;
    personal_organisation: boolean;
    created_at: string;
    updated_at: string;
}

export interface Page {
    id: number;
    title: string;
    description?: string;
    slug: string;
    website_id: number;
    content_blocks: {
        content_block_type_id: string; //?
        content: {
            [key: string]: string;
        };
        content_with_urls: {
            [key: string]: string;
        };
    }[];
    created_at: string;
    updated_at: string;
}

export interface PageIdea {
    id: number;
    title: string;
    summary: string;
    created_at: string;
    updated_at: string;
    version_number: number;
    is_latest_version: boolean;
    conversation: {
        id: number;
        title: string;
        created_at: string;
    };
}

export interface ContentBlockType {
    id: number;
    name: string;
    slug: string;
    fields: { label: string; type: string; reference_block_type_id?: string; slug: string }[];
    organisation_id: number;
    is_default: boolean;
    created_at: string;
    updated_at: string;
}

export interface ContentBlock {
    id: number;
    content_block_type_id: number;
    content: Record<string, string>;
    content_with_urls: Record<string, string>;
    description: string;
    created_at: string;
    updated_at: string;
}

export interface GlobalContentBlock {
    id: number;
    website_id: number;
    content_block_id: number;
    content_block: ContentBlock & {
        block_type: ContentBlockType;
    };
    created_at: string;
    updated_at: string;
}

export interface ThirdPartyProvider {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface ThirdPartyVariable {
    id: number;
    name: string;
    description: string | null;
    is_secret: boolean;
    third_party_provider_id: number;
    provider?: ThirdPartyProvider;
    created_at: string;
    updated_at: string;
}

export interface ThirdPartyProviderable {
    id: number;
    third_party_provider_id: number;
    providerable_id: number;
    providerable_type: string;
    variable_values: ThirdPartyVariableValue[];
}

export interface ThirdPartyVariableValue {
    id: number;
    third_party_variable_id: number;
    third_party_providerable_id: number;
    value: string;
    created_at: string;
    updated_at: string;
}

export interface PrivateFile {
    id: number;
    name: string;
    description: string | null;
    content_type: 'ebook' | 'audiobook' | 'video' | 'document' | 'other';
    file_name: string;
    mime_type: string;
    file_size: string;
    created_at: string;
    updated_at: string;
}

export interface Customer {
    id: number;
    name: string;
    email: string;
    created_at: string;
    updated_at: string;
    products: Array<{
        id: number;
        name: string;
        price: number;
        currency: string;
        organisation_id: number;
        pivot?: {
            payment_id: number;
            purchased_at: string;
            created_at: string;
        };
    }>;
}
