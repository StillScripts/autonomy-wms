import { useAppForm, type FormTextContext, type ReusableForm } from '@/components/forms/form-context';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { ThirdPartyProvider } from '@/types';
import { router } from '@inertiajs/react';
import { type } from 'arktype';

const ThirdPartyProviderSchema = type({
    name: 'string',
    description: 'string?',
    is_active: 'boolean',
});

type ThirdPartyProviderSchema = typeof ThirdPartyProviderSchema.infer;

interface Props extends ReusableForm<ThirdPartyProviderSchema> {
    provider?: ThirdPartyProvider;
}

const textContent: FormTextContext = {
    create: {
        title: 'Create Provider',
        description: 'Define a new provider for your organisation',
        buttonText: 'Create Provider',
    },
    edit: {
        title: 'Edit Provider',
        description: 'Modify your existing provider',
        buttonText: 'Update Provider',
    },
};

export default function Form({
    mode,
    provider,
    defaultValues = {
        name: '',
        description: '',
        is_active: true,
    },
}: Props) {
    const form = useAppForm({
        defaultValues,
        onSubmit: async (data) => {
            await new Promise((resolve) => setTimeout(resolve, 1000));
            if (mode === 'create') {
                router.post(route('system.third-party-providers.store'), data.value);
            } else {
                if (!provider?.id) {
                    console.error('provider.id is required for edit mode');
                    return;
                }
                router.put(route('system.third-party-providers.update', { third_party_provider: provider.id }), data.value);
            }
        },
        validators: {
            onChange: ThirdPartyProviderSchema,
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
                    <CardTitle className="text-xl">{textContent[mode].title}</CardTitle>
                    <CardDescription>{textContent[mode].description}</CardDescription>
                </CardHeader>
                <CardContent className="space-y-6">
                    <form.AppField
                        name="name"
                        children={(field) => {
                            return <field.FormInput name={field.name} label="Name" />;
                        }}
                    />
                    <form.AppField
                        name="description"
                        children={(field) => {
                            return <field.FormTextarea name={field.name} label="Description" />;
                        }}
                    />
                    <form.AppField
                        name="is_active"
                        children={(field) => {
                            return <field.FormSwitch name={field.name} label="Active" />;
                        }}
                    />
                </CardContent>
                <CardFooter>
                    <form.AppForm>
                        <form.SubscribeButton label={provider ? 'Update Provider' : 'Create Provider'} />
                    </form.AppForm>
                </CardFooter>
            </Card>
        </form>
    );
}
