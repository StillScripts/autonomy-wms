import { useAppForm } from '@/components/forms/form-context';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Transition } from '@headlessui/react';
import { router } from '@inertiajs/react';

interface WebsitesFormProps {
    mode: 'create' | 'edit';
    defaultValues?: {
        title: string;
        domain: string;
        description: string;
        logo: File | string | null;
    };
    websiteId?: number;
}

const textContent = {
    create: {
        title: 'Create Website',
        description: 'Define a new website for your organisation',
        buttonText: 'Create Website',
    },
    edit: {
        title: 'Edit Website',
        description: 'Modify your existing website',
        buttonText: 'Update Website',
    },
};

export default function WebsitesForm({
    mode,
    defaultValues = {
        title: '',
        domain: '',
        description: '',
        logo: null as File | string | null,
    },
    websiteId,
}: WebsitesFormProps) {
    const form = useAppForm({
        defaultValues,
        onSubmit: async (data) => {
            // Debug logs to see what we're working with
            console.log('Form submission data:', data);
            console.log('Form values:', data.value);

            const formData = new FormData();
            Object.entries(data.value).forEach(([key, value]) => {
                if (value !== null) {
                    formData.append(key, value);
                    console.log(`Adding to FormData - ${key}:`, value);
                }
            });

            // Log final FormData contents
            console.log('FormData entries:');
            for (const pair of formData.entries()) {
                console.log(pair[0], ':', pair[1]);
            }

            if (mode === 'create') {
                router.post(route('websites.store'), formData, {
                    forceFormData: true,
                });
            } else {
                // Add _method field for Laravel method spoofing
                formData.append('_method', 'PUT');

                // Use POST but with _method=PUT for proper file handling
                router.post(route('websites.update', websiteId), formData, {
                    forceFormData: true,
                    onSuccess: () => {
                        console.log('Update successful');
                    },
                    onError: (errors) => {
                        console.error('Update failed:', errors);
                    },
                });
            }
        },
    });

    return (
        <Card>
            <CardHeader>
                <CardTitle>{textContent[mode]?.title}</CardTitle>
            </CardHeader>
            <CardContent>
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        console.log('Form submit event triggered'); // Debug log
                        form.handleSubmit();
                    }}
                    className="space-y-6"
                >
                    <form.AppField
                        name="title"
                        validators={{
                            onChange: ({ value }) =>
                                !value ? 'A title is required' : value.length < 2 ? 'Title must be at least 2 characters' : undefined,
                        }}
                        children={(field) => {
                            return (
                                <field.FormInput
                                    name={field.name}
                                    label="Website name"
                                    required
                                    className="mt-1 block w-full"
                                    autoComplete="name"
                                    placeholder="My New Site"
                                />
                            );
                        }}
                    />
                    <form.AppField
                        name="domain"
                        validators={{
                            onChange: ({ value }) =>
                                !value ? 'A domain is required' : value.length < 2 ? 'Domain must be at least 2 characters' : undefined,
                        }}
                        children={(field) => {
                            return <field.FormInput name={field.name} label="Domain" required autoComplete="name" placeholder="my-new-site.com" />;
                        }}
                    />
                    <form.AppField
                        name="description"
                        children={(field) => {
                            return (
                                <field.FormInput
                                    name={field.name}
                                    label="Description"
                                    autoComplete="name"
                                    placeholder="A description of the website (optional)"
                                />
                            );
                        }}
                    />
                    <form.AppField
                        name="logo"
                        children={(field) => {
                            return (
                                <field.FormFile
                                    name={field.name}
                                    label="Logo"
                                    autoComplete="name"
                                    placeholder="Upload a logo for your website"
                                    filePreview={defaultValues.logo?.toString() ?? null}
                                />
                            );
                        }}
                    />
                    <div className="flex items-center gap-4">
                        <Button
                            type="submit"
                            onClick={() => console.log('Save button clicked')} // Debug log
                        >
                            Save
                        </Button>

                        <Transition
                            show={form.state.isSubmitSuccessful}
                            enter="transition ease-in-out"
                            enterFrom="opacity-0"
                            leave="transition ease-in-out"
                            leaveTo="opacity-0"
                        >
                            <p className="text-muted-foreground text-sm">Saved</p>
                        </Transition>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
