export interface ThirdPartyProviderableCreate {
    availableProviders: Array<{
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
    }>;
}
