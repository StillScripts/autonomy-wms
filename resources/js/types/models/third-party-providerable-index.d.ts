export interface ThirdPartyProviderableIndex {
    providerConfigurations: Array<{
        provider: {
            value: string;
            display_name: string;
            variables: Record<
                string,
                {
                    name: string;
                    description: string;
                    is_secret: boolean;
                    is_test: boolean;
                }
            >;
        };
        provider_name: string;
        variables: Array<{
            id: number;
            provider: string;
            variable_key: string;
            value: string;
            created_at: string;
            updated_at: string;
        }>;
        variable_count: number;
    }>;
    availableProviders: Array<{
        value: string;
        display_name: string;
        variables: Record<
            string,
            {
                label: string;
                type: string;
                required: boolean;
                description?: string;
            }
        >;
    }>;
}
