import { useAppForm } from '@/components/forms/form-context';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { router } from '@inertiajs/react';
import { type } from 'arktype';

interface AvailableProvider {
    value: string;
    display_name: string;
    variables: Record<string, unknown>;
}

const ThirdPartyProviderableSchema = type({
    provider: 'string',
});

type ThirdPartyProviderableSchema = typeof ThirdPartyProviderableSchema.infer;

interface Props {
    availableProviders: AvailableProvider[];
}

export default function CreateForm({ availableProviders }: Props) {
    const form = useAppForm({
        defaultValues: { provider: '' },
        onSubmit: async (data) => {
            router.post(route('third-parties.store'), { provider: data.value.provider });
        },
        validators: {
            onChange: ThirdPartyProviderableSchema,
        },
    });

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                e.stopPropagation();
                form.handleSubmit();
            }}
        >
            <Card>
                <CardHeader>
                    <CardTitle className="text-xl">Add Third Party Provider</CardTitle>
                    <CardDescription>Select a provider to add to your organisation</CardDescription>
                </CardHeader>
                <CardContent className="space-y-6">
                    <form.AppField
                        name="provider"
                        children={(field) => (
                            <field.FormSelect
                                name={field.name}
                                label="Provider"
                                options={availableProviders.map((provider) => ({
                                    label: provider.display_name,
                                    value: provider.value,
                                }))}
                            />
                        )}
                    />
                </CardContent>
                <CardFooter>
                    <form.AppForm>
                        <form.SubscribeButton label="Create Provider" />
                    </form.AppForm>
                </CardFooter>
            </Card>
        </form>
    );
}
