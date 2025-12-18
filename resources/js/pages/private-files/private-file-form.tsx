import { ReusableForm, useAppForm } from '@/components/forms/form-context';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Transition } from '@headlessui/react';
import { router } from '@inertiajs/react';
import { type } from 'arktype';

const PrivateFileSchema = type({
    name: 'string',
    description: 'string?',
    content_type: 'string',
    file: 'File',
});

type PrivateFileSchema = typeof PrivateFileSchema.infer;

interface Props extends ReusableForm<PrivateFileSchema> {
    contentTypes: string[];
}

const textContent = {
    create: {
        title: 'Upload Private File',
        description: 'Add a new private file to your organisation',
        buttonText: 'Upload File',
    },
    edit: {
        title: 'Edit Private File',
        description: 'Modify your existing private file',
        buttonText: 'Update File',
    },
};

const contentTypeLabels: Record<string, string> = {
    ebook: 'E-book',
    audiobook: 'Audiobook',
    video: 'Video',
    document: 'Document',
    other: 'Other',
};

export default function PrivateFileForm({
    mode,
    contentTypes,
    defaultValues = {
        name: '',
        description: '',
        content_type: 'document',
        file: undefined,
    },
}: Props) {
    const form = useAppForm({
        defaultValues,
        onSubmit: async (data) => {
            console.log('[PrivateFileForm] Starting file upload submission');
            console.log('[PrivateFileForm] Form data:', data.value);

            // Log file details if present
            if (data.value.file) {
                console.log('[PrivateFileForm] File details:', {
                    name: data.value.file.name,
                    size: data.value.file.size,
                    sizeInMB: (data.value.file.size / (1024 * 1024)).toFixed(2) + ' MB',
                    type: data.value.file.type,
                    lastModified: new Date(data.value.file.lastModified).toISOString(),
                });

                // Check if it's an MP3 file
                if (data.value.file.type === 'audio/mpeg' || data.value.file.name.toLowerCase().endsWith('.mp3')) {
                    console.log('[PrivateFileForm] Detected MP3 file');
                }

                // Check file size against backend limit (512MB)
                const maxSizeBytes = 512 * 1024 * 1024; // 512MB
                if (data.value.file.size > maxSizeBytes) {
                    console.error('[PrivateFileForm] File size exceeds 512MB limit:', {
                        fileSize: data.value.file.size,
                        maxSize: maxSizeBytes,
                        exceedsBy: ((data.value.file.size - maxSizeBytes) / (1024 * 1024)).toFixed(2) + ' MB',
                    });
                }
            }

            const formData = new FormData();
            Object.entries(data.value).forEach(([key, value]) => {
                if (value !== null && value !== undefined) {
                    if (typeof value === 'boolean') {
                        formData.append(key, value ? '1' : '0');
                        console.log(`[PrivateFileForm] Appending boolean ${key}:`, value ? '1' : '0');
                    } else if (value instanceof File) {
                        formData.append(key, value);
                        console.log(`[PrivateFileForm] Appending file ${key}:`, value.name);
                    } else {
                        formData.append(key, String(value));
                        console.log(`[PrivateFileForm] Appending ${key}:`, String(value));
                    }
                }
            });

            console.log('[PrivateFileForm] FormData prepared, initiating upload...');

            if (mode === 'create') {
                router.post(route('private-files.store'), formData, {
                    forceFormData: true,
                    onStart: () => {
                        console.log('[PrivateFileForm] Upload started');
                    },
                    onProgress: (progress) => {
                        console.log('[PrivateFileForm] Upload progress:', progress);
                    },
                    onSuccess: (page) => {
                        console.log('[PrivateFileForm] Upload successful', page);
                    },
                    onError: (errors) => {
                        console.error('[PrivateFileForm] Upload failed with errors:', errors);
                    },
                    onFinish: () => {
                        console.log('[PrivateFileForm] Upload finished');
                    },
                });
            }
        },
    });

    return (
        <Card>
            <CardHeader>
                <CardTitle>{textContent[mode].title}</CardTitle>
            </CardHeader>
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.handleSubmit();
                }}
            >
                <CardContent className="space-y-6">
                    <form.AppField
                        name="name"
                        validators={{
                            onChange: ({ value }) =>
                                !value ? 'A name is required' : value.length < 2 ? 'Name must be at least 2 characters' : undefined,
                        }}
                        children={(field) => {
                            return <field.FormInput name={field.name} label="File Name" required placeholder="My Important Document" />;
                        }}
                    />

                    <form.AppField
                        name="description"
                        children={(field) => {
                            return (
                                <field.FormTextarea
                                    name={field.name}
                                    label="Description"
                                    placeholder="Brief description of the file contents (optional)"
                                    rows={3}
                                />
                            );
                        }}
                    />

                    <form.AppField
                        name="content_type"
                        validators={{
                            onChange: ({ value }) => (!value ? 'Content type is required' : undefined),
                        }}
                        children={(field) => (
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Content Type</label>
                                <Select value={field.state.value} onValueChange={(value) => field.setValue(value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select content type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {contentTypes.map((type) => (
                                            <SelectItem key={type} value={type}>
                                                {contentTypeLabels[type] || type}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {field.state.meta.errors && <p className="text-sm text-red-600">{field.state.meta.errors.join(', ')}</p>}
                            </div>
                        )}
                    />

                    {mode === 'create' && (
                        <form.AppField
                            name="file"
                            validators={{
                                onChange: ({ value }) => {
                                    console.log('[PrivateFileForm] File field changed:', value);

                                    if (!value) {
                                        return 'A file is required';
                                    }

                                    // Log validation details
                                    if (value instanceof File) {
                                        console.log('[PrivateFileForm] File validation:', {
                                            name: value.name,
                                            size: value.size,
                                            sizeInMB: (value.size / (1024 * 1024)).toFixed(2) + ' MB',
                                            type: value.type,
                                            isMP3: value.type === 'audio/mpeg' || value.name.toLowerCase().endsWith('.mp3'),
                                        });

                                        // Check file size
                                        const maxSizeBytes = 512 * 1024 * 1024; // 512MB
                                        if (value.size > maxSizeBytes) {
                                            const errorMsg = `File size (${(value.size / (1024 * 1024)).toFixed(2)} MB) exceeds the 512MB limit`;
                                            console.error('[PrivateFileForm] Validation error:', errorMsg);
                                            return errorMsg;
                                        }
                                    }

                                    return undefined;
                                },
                            }}
                            children={(field) => {
                                return <field.FormFile name={field.name} label="File" required accept="*/*" filePreview={null} />;
                            }}
                        />
                    )}
                </CardContent>

                <CardFooter className="flex items-center gap-4">
                    <Button type="submit" disabled={form.state.isSubmitting}>
                        {form.state.isSubmitting ? 'Uploading...' : textContent[mode].buttonText}
                    </Button>

                    <Transition
                        show={form.state.isSubmitSuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <p className="text-muted-foreground text-sm">Uploaded successfully</p>
                    </Transition>
                </CardFooter>
            </form>
        </Card>
    );
}
