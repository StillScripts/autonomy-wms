import { useAppForm } from '@/components/forms/form-context';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { allFieldTypes } from '@/components/ui/form-fields';
import { capitalize } from '@/lib/utils';
import { router } from '@inertiajs/react';

interface ContentBlockTypesFormProps {
    mode: 'create' | 'edit';
    defaultValues?: {
        name: string;
        is_default: boolean;
        fields: { label: string; type: string }[];
    };
    contentBlockTypeSlug?: string;
    customContentBlockTypeOptions?: { label: string; id: string }[];
}

const textContent = {
    create: {
        title: 'Create Content Block Type',
        description: 'Define a new content block type for your organisation',
        buttonText: 'Create Content Block Type',
    },
    edit: {
        title: 'Edit Content Block Type',
        description: 'Modify your existing content block type',
        buttonText: 'Update Content Block Type',
    },
};

export function ContentBlockTypesForm({
    mode,
    defaultValues = {
        name: '',
        is_default: false,
        fields: [] as { label: string; type: string }[],
    },
    contentBlockTypeSlug,
    customContentBlockTypeOptions = [],
}: ContentBlockTypesFormProps) {
    const form = useAppForm({
        defaultValues,
        onSubmit: async (data) => {
            await new Promise((resolve) => setTimeout(resolve, 1000));
            if (mode === 'create') {
                router.post(route('content-block-types.store'), data.value);
            } else {
                if (!contentBlockTypeSlug) {
                    console.error('contentBlockTypeSlug is required for edit mode');
                    return;
                }
                router.put(route('content-block-types.update', { content_block_type: contentBlockTypeSlug }), data.value);
            }
        },
    });

    const contentBlockTypeOptions = [...customContentBlockTypeOptions, ...allFieldTypes];

    return (
        <Card>
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    form.handleSubmit();
                }}
                className="space-y-6"
            >
                <CardHeader>
                    <CardTitle>{textContent[mode]?.title}</CardTitle>
                    <CardDescription>{textContent[mode]?.description}</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    <form.AppField
                        name="name"
                        children={(field) => {
                            return (
                                <field.FormInput
                                    name={field.name}
                                    label="Content Block Type Name"
                                    required
                                    className="mt-1 block w-full"
                                    autoComplete="name"
                                    placeholder="My New Content Block Type"
                                />
                            );
                        }}
                    />
                    <form.Field
                        name="fields"
                        mode="array"
                        children={(fields) => (
                            <div className="space-y-4">
                                <h4 className="text-lg font-medium">Content Block Fields</h4>
                                <div className="space-y-4">
                                    {!fields.state.value.length ? (
                                        <span className="text-muted-foreground text-sm">No fields found.</span>
                                    ) : (
                                        fields.state.value.map((_, i) => (
                                            <div className="grid gap-2 sm:grid-cols-2 sm:gap-4" key={i}>
                                                <form.AppField
                                                    name={`fields[${i}].label`}
                                                    children={(field) => {
                                                        return <field.FormInput name={field.name} label="Field Label" required />;
                                                    }}
                                                />
                                                <form.AppField
                                                    name={`fields[${i}].type`}
                                                    children={(field) => {
                                                        return (
                                                            <field.FormSelect
                                                                name={field.name}
                                                                label="Field Type"
                                                                options={contentBlockTypeOptions.map((type) =>
                                                                    typeof type === 'string'
                                                                        ? { value: type, label: capitalize(type) }
                                                                        : { value: type.id.toString(), label: capitalize(type.label) },
                                                                )}
                                                            />
                                                        );
                                                    }}
                                                />
                                                <Button variant="destructive" size="sm" type="button" onClick={() => fields.removeValue(i)}>
                                                    Remove
                                                </Button>
                                            </div>
                                        ))
                                    )}
                                </div>
                                <Button variant="outline" size="sm" type="button" onClick={() => fields.pushValue({ label: '', type: '' })}>
                                    Add field
                                </Button>
                            </div>
                        )}
                    />
                </CardContent>
                <CardFooter>
                    <form.AppForm>
                        <form.SubscribeButton label={textContent[mode]?.buttonText} />
                    </form.AppForm>
                </CardFooter>
            </form>
        </Card>
    );
}
