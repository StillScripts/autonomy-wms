export interface ThirdPartyProviderable {
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
    currentValues: Record<string, string>;
}
