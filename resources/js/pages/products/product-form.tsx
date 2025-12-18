import { useAppForm } from '@/components/forms/form-context';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { router } from '@inertiajs/react';

interface PrivateFileAssociation {
    id: number;
    sort_order: number;
    [key: string]: unknown;
}

interface ProductFormData {
    name: string;
    description: string;
    price: number;
    currency: string;
    active: boolean;
    private_files: PrivateFileAssociation[];
    product_types: string[];
    [key: string]: unknown;
}

interface Props {
    mode: 'create' | 'edit';
    defaultValues?: Partial<ProductFormData>;
    productId?: number;
    availablePrivateFiles: Array<{
        id: number;
        name: string;
        description: string | null;
        content_type: string;
        file_size: string;
    }>;
}

const textContent = {
    create: {
        title: 'Create Product',
        description: 'Create a new product for your organisation',
        buttonText: 'Create Product',
    },
    edit: {
        title: 'Edit Product Files',
        description: 'Manage private files associated with this product',
        buttonText: 'Update Files',
    },
};

export default function ProductForm({ mode, defaultValues = {}, productId, availablePrivateFiles }: Props) {
    const form = useAppForm({
        defaultValues: {
            name: '',
            description: '',
            price: 0,
            currency: 'USD',
            active: true,
            private_files: [],
            product_types: [],
            ...defaultValues,
        },
        onSubmit: async (data) => {
            const formData = {
                private_files: data.value.private_files.map((file) => ({
                    id: file.id,
                    sort_order: file.sort_order,
                })),
                product_types: data.value.product_types,
            };

            if (mode === 'create') {
                router.post(route('products.store'), formData);
            } else {
                if (!productId) {
                    console.error('productId is required for edit mode');
                    return;
                }
                router.put(route('products.update', { product: productId }), formData);
            }
        },
    });

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
                    <CardTitle>{textContent[mode].title}</CardTitle>
                    <CardDescription>{textContent[mode].description}</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="space-y-4">
                        <div className="grid gap-2">
                            <label className="text-sm font-medium">Product Name</label>
                            <p className="text-muted-foreground text-sm">{form.state.values.name}</p>
                        </div>
                        <div className="grid gap-2">
                            <label className="text-sm font-medium">Description</label>
                            <p className="text-muted-foreground text-sm">{form.state.values.description || 'No description'}</p>
                        </div>
                        <div className="grid gap-2">
                            <label className="text-sm font-medium">Price</label>
                            <p className="text-muted-foreground text-sm">
                                {new Intl.NumberFormat('en-US', {
                                    style: 'currency',
                                    currency: form.state.values.currency,
                                }).format(form.state.values.price)}
                            </p>
                        </div>
                        <div className="grid gap-2">
                            <label className="text-sm font-medium">Status</label>
                            <p className="text-muted-foreground text-sm">{form.state.values.active ? 'Active' : 'Inactive'}</p>
                        </div>
                    </div>

                    <form.Field
                        name="product_types"
                        mode="array"
                        children={() => (
                            <div className="space-y-4">
                                <form.AppField
                                    name="product_types"
                                    children={(field) => {
                                        return <field.FormTagInput name={field.name} label="Associated Product Types" />;
                                    }}
                                />
                            </div>
                        )}
                    />

                    <form.Field
                        name="private_files"
                        mode="array"
                        children={(files) => (
                            <div className="space-y-4">
                                <h4 className="text-lg font-medium">Associated Private Files</h4>
                                <div className="space-y-4">
                                    {!files.state.value?.length ? (
                                        <span className="text-muted-foreground text-sm">No files associated.</span>
                                    ) : (
                                        files.state.value.map((_, i) => (
                                            <div className="grid gap-2 sm:grid-cols-2 sm:gap-4" key={i}>
                                                <form.AppField
                                                    name={`private_files[${i}].id`}
                                                    children={(field) => {
                                                        return (
                                                            <field.FormSelect
                                                                name={field.name}
                                                                label="Private File"
                                                                options={availablePrivateFiles.map((file) => ({
                                                                    value: file.id.toString(),
                                                                    label: `${file.name} (${file.content_type})`,
                                                                }))}
                                                            />
                                                        );
                                                    }}
                                                />
                                                <form.AppField
                                                    name={`private_files[${i}].sort_order`}
                                                    children={(field) => {
                                                        return <field.FormInput name={field.name} label="Sort Order" type="number" min="0" />;
                                                    }}
                                                />
                                                <Button variant="destructive" size="sm" type="button" onClick={() => files.removeValue(i)}>
                                                    Remove
                                                </Button>
                                            </div>
                                        ))
                                    )}
                                </div>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    type="button"
                                    onClick={() => files.pushValue({ id: 0, sort_order: files.state.value?.length ?? 0 })}
                                >
                                    Add file
                                </Button>
                            </div>
                        )}
                    />
                </CardContent>
                <CardFooter>
                    <form.AppForm>
                        <form.SubscribeButton label={textContent[mode].buttonText} />
                    </form.AppForm>
                </CardFooter>
            </form>
        </Card>
    );
}
