import { useAppForm } from '@/components/forms/form-context';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { router } from '@inertiajs/react';
import { type } from 'arktype';

const PageIdeaSchema = type({
    message: 'string',
});

type PageIdeaSchema = typeof PageIdeaSchema.infer;

interface Conversation {
    id: number;
    title: string;
}

interface Props {
    conversation: Conversation;
    apiConnectionStatus: boolean;
    defaultMessage?: string;
}

export default function PageIdeasForm({ conversation, apiConnectionStatus, defaultMessage = '' }: Props) {
    const form = useAppForm({
        defaultValues: {
            message: defaultMessage,
        },
        onSubmit: async (data) => {
            router.post('/page-ideas/generate', {
                conversation_id: conversation.id,
                message: data.value.message.trim(),
            });
        },
        validators: {
            onChange: PageIdeaSchema,
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
                    <CardTitle>Page Idea Details</CardTitle>
                    <CardDescription>Describe what you want to improve or change about your landing page</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    <Badge variant={apiConnectionStatus ? 'default' : 'destructive'}>
                        {apiConnectionStatus ? 'API Connected' : 'API Disconnected'}
                    </Badge>
                    {!apiConnectionStatus && (
                        <Alert variant="destructive">
                            <AlertDescription>API is currently disconnected. Please try again later.</AlertDescription>
                        </Alert>
                    )}
                    <form.AppField
                        name="message"
                        validators={{
                            onChange: ({ value }) => {
                                if (!value.trim()) {
                                    return 'Message is required';
                                }
                                if (value.length > 1000) {
                                    return 'Message must be less than 1000 characters';
                                }
                                return undefined;
                            },
                        }}
                        children={(field) => {
                            return (
                                <field.FormTextarea
                                    name={field.name}
                                    label="Page Idea Description"
                                    placeholder="e.g., Make it more focused on enterprise customers, add a testimonials section, or make the copy more compelling..."
                                    rows={4}
                                />
                            );
                        }}
                    />
                </CardContent>
                <CardFooter>
                    <form.AppForm>
                        <form.SubscribeButton
                            label="Generate Page Idea"
                            // @ts-expect-error: Button disables itself based on form state, but we want to force disable if API is disconnected
                            disabled={!apiConnectionStatus}
                        />
                    </form.AppForm>
                </CardFooter>
            </Card>
        </form>
    );
}
