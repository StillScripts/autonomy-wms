import { useAppForm, type FormTextContext, type ReusableForm } from '@/components/forms/form-context';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { ThirdPartyProvider, ThirdPartyVariable } from '@/types/models';
import { router } from '@inertiajs/react';
import { type } from 'arktype';

const ThirdPartyVariableSchema = type({
    name: 'string',
    'description?': 'string',
    is_secret: 'boolean',
    'third_party_provider_id?': 'string',
});

type ThirdPartyVariableSchema = typeof ThirdPartyVariableSchema.infer;

interface Props extends ReusableForm<ThirdPartyVariableSchema> {
    variable?: ThirdPartyVariable;
    providers: ThirdPartyProvider[];
}

const textContent: FormTextContext = {
    create: {
        title: 'Create Variable',
        description: 'Define a new variable for your provider',
        buttonText: 'Create Variable',
    },
    edit: {
        title: 'Edit Variable',
        description: 'Modify your existing variable',
        buttonText: 'Update Variable',
    },
};

export default function Form({
    mode,
    variable,
    providers,
    defaultValues = {
        name: '',
        description: '',
        is_secret: false,
        third_party_provider_id: undefined,
    },
}: Props) {
    const form = useAppForm({
        defaultValues,
        onSubmit: async (data) => {
            if (mode === 'create') {
                router.post(route('system.third-party-variables.store'), data.value);
            } else {
                if (!variable?.id) {
                    console.error('variable.id is required for edit mode');
                    return;
                }
                router.put(route('system.third-party-variables.update', { thirdPartyVariable: variable.id }), data.value);
            }
        },
        validators: {
            onChange: ThirdPartyVariableSchema,
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
                        name="third_party_provider_id"
                        children={(field) => {
                            return (
                                <field.FormSelect
                                    name={field.name}
                                    label="Provider"
                                    options={providers.map((provider) => ({
                                        label: provider.name,
                                        value: provider.id.toString(),
                                    }))}
                                />
                            );
                        }}
                    />
                    <form.AppField
                        name="is_secret"
                        children={(field) => {
                            return <field.FormSwitch name={field.name} label="Secret Variable" />;
                        }}
                    />
                </CardContent>
                <CardFooter>
                    <form.AppForm>
                        <form.SubscribeButton label={textContent[mode].buttonText} />
                    </form.AppForm>
                </CardFooter>
            </Card>
        </form>
    );
}
