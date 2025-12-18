'use client';

import { useAppForm } from '@/components/forms/form-context';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { router } from '@inertiajs/react';
import { CheckCircle, Lightbulb, Save, XCircle } from 'lucide-react';
import { useState } from 'react';

// Type definitions
interface Section {
    title: string;
    description: string;
    justification: string;
}

interface LandingPageIdea {
    title: string;
    summary: string;
    message: string;
    sections: Section[];
}

type SectionStatus = 'good' | 'bad' | 'neutral';

interface Message {
    role: 'user' | 'assistant';
    content: string;
}

interface Conversation {
    id: number;
    title: string;
    messages: Message[];
}

interface Props {
    conversation: Conversation;
    apiConnectionStatus: boolean;
    onSuccess?: () => void;
    initialPageIdea?: LandingPageIdea;
}

export function LandingPageIdea({ conversation, onSuccess, initialPageIdea }: Props) {
    const idea = initialPageIdea!;
    const [isSaving, setIsSaving] = useState(false);

    const form = useAppForm({
        defaultValues: {
            feedbackItems: idea.sections.map(() => ({
                feedback: '',
                status: 'neutral' as SectionStatus,
            })),
        },
        onSubmit: async (data) => {
            if (!idea) return;

            const goodSections = idea.sections
                .map((section: Section, index: number) => ({ section, index }))
                .filter(({ index }) => data.value.feedbackItems[index]?.status === 'good')
                .map(({ section }) => section.title);

            const badSectionsWithFeedback = idea.sections
                .map((section: Section, index: number) => ({
                    section,
                    feedbackItem: data.value.feedbackItems[index],
                }))
                .filter(({ feedbackItem }) => feedbackItem?.status === 'bad');

            let feedback = 'I have reviewed the landing page idea. Please generate a new version based on my feedback.\n';
            if (goodSections.length > 0) {
                feedback += `\nThe following sections are good and I'd like to keep them or something similar: ${goodSections.join(', ')}.\n`;
            }
            if (badSectionsWithFeedback.length > 0) {
                const badFeedbackText = badSectionsWithFeedback
                    .map(({ section, feedbackItem }) => {
                        let text = section.title;
                        if (feedbackItem.feedback) {
                            text += ` (feedback: "${feedbackItem.feedback}")`;
                        }
                        return text;
                    })
                    .join(', ');
                feedback += `\nThe following sections need work: ${badFeedbackText}.\n`;
            }

            if (goodSections.length === 0 && badSectionsWithFeedback.length === 0) {
                feedback = 'Please generate a completely new and different idea.';
            }

            try {
                await router.post(
                    '/page-ideas/generate',
                    {
                        conversation_id: conversation.id,
                        message: feedback,
                    },
                    {
                        preserveScroll: true,
                        onSuccess: () => {
                            // The response will be handled by the page reload
                            console.log('New page idea generated successfully');
                        },
                        onError: (errors) => {
                            console.error('Failed to generate new page idea:', errors);
                        },
                    },
                );
            } catch (error) {
                console.error('Error generating new page idea:', error);
            }
        },
    });

    const handleApprove = async () => {
        setIsSaving(true);
        try {
            // Here you would typically save the approved page idea
            // For now, we'll just show a success message
            setTimeout(() => {
                alert('Landing page idea saved successfully!');
                setIsSaving(false);
                if (onSuccess) onSuccess();
            }, 1000);
        } catch (error) {
            console.error('Error saving page idea:', error);
            setIsSaving(false);
        }
    };

    const getSectionStatusBadge = (status: SectionStatus) => {
        switch (status) {
            case 'good':
                return (
                    <Badge variant="default" className="border-green-200 bg-green-100 text-green-800">
                        Good
                    </Badge>
                );
            case 'bad':
                return (
                    <Badge variant="destructive" className="border-red-200 bg-red-100 text-red-800">
                        Needs Work
                    </Badge>
                );
            default:
                return (
                    <Badge variant="outline" className="bg-gray-50 text-gray-600">
                        Not Rated
                    </Badge>
                );
        }
    };

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
                    <CardTitle className="text-2xl">{idea.title}</CardTitle>
                    <CardDescription className="text-base">{idea.summary}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4">
                        <p className="font-medium text-blue-800">AI Message:</p>
                        <p className="mt-1 text-blue-700">{idea.message}</p>
                    </div>

                    <div className="space-y-4">
                        <h3 className="text-lg font-semibold">Sections ({idea.sections.length})</h3>

                        <form.Field
                            name="feedbackItems"
                            mode="array"
                            children={(fields) => (
                                <div className="space-y-4">
                                    {fields.state.value.map((_, i) => {
                                        const section = idea.sections[i];
                                        return (
                                            <Card key={i} className="border-l-4 border-l-blue-500">
                                                <CardHeader className="pb-3">
                                                    <div className="flex items-start justify-between">
                                                        <div className="flex-1">
                                                            <CardTitle className="text-lg">{section.title}</CardTitle>
                                                            <CardDescription className="mt-1">{section.description}</CardDescription>
                                                        </div>
                                                        <div className="ml-4">
                                                            <form.Field
                                                                name={`feedbackItems[${i}].status`}
                                                                children={(statusField) => getSectionStatusBadge(statusField.state.value)}
                                                            />
                                                        </div>
                                                    </div>
                                                </CardHeader>
                                                <CardContent className="pt-0">
                                                    <div className="mb-4 rounded-lg bg-gray-50 p-3">
                                                        <p className="text-sm text-gray-700">
                                                            <span className="font-medium">Why it matters:</span> {section.justification}
                                                        </p>
                                                    </div>

                                                    <div className="flex gap-2">
                                                        <form.Field
                                                            name={`feedbackItems[${i}].status`}
                                                            children={(statusField) => (
                                                                <Button
                                                                    type="button"
                                                                    variant={statusField.state.value === 'good' ? 'default' : 'outline'}
                                                                    size="sm"
                                                                    onClick={() => {
                                                                        const currentStatus = statusField.state.value;
                                                                        const newStatus = currentStatus === 'good' ? 'neutral' : 'good';
                                                                        statusField.handleChange(newStatus);
                                                                    }}
                                                                    className={
                                                                        statusField.state.value === 'good'
                                                                            ? 'bg-green-600 hover:bg-green-700'
                                                                            : 'hover:border-green-300 hover:bg-green-50 hover:text-green-700'
                                                                    }
                                                                >
                                                                    <CheckCircle className="mr-1 h-4 w-4" />
                                                                    Good
                                                                </Button>
                                                            )}
                                                        />
                                                        <form.Field
                                                            name={`feedbackItems[${i}].status`}
                                                            children={(statusField) => (
                                                                <Button
                                                                    type="button"
                                                                    variant={statusField.state.value === 'bad' ? 'destructive' : 'outline'}
                                                                    size="sm"
                                                                    onClick={() => {
                                                                        const currentStatus = statusField.state.value;
                                                                        const newStatus = currentStatus === 'bad' ? 'neutral' : 'bad';
                                                                        statusField.handleChange(newStatus);
                                                                    }}
                                                                    className={
                                                                        statusField.state.value !== 'bad'
                                                                            ? 'hover:border-red-300 hover:bg-red-50 hover:text-red-700'
                                                                            : ''
                                                                    }
                                                                >
                                                                    <XCircle className="mr-1 h-4 w-4" />
                                                                    Needs Work
                                                                </Button>
                                                            )}
                                                        />
                                                    </div>
                                                    <form.Field
                                                        name={`feedbackItems[${i}].status`}
                                                        children={(statusField) =>
                                                            statusField.state.value === 'bad' && (
                                                                <div className="mt-4">
                                                                    <form.AppField
                                                                        name={`feedbackItems[${i}].feedback`}
                                                                        children={(field) => {
                                                                            return (
                                                                                <field.FormTextarea
                                                                                    name={field.name}
                                                                                    label="Feedback"
                                                                                    placeholder="What would you like to change about this section?"
                                                                                    rows={3}
                                                                                />
                                                                            );
                                                                        }}
                                                                    />
                                                                </div>
                                                            )
                                                        }
                                                    />
                                                </CardContent>
                                            </Card>
                                        );
                                    })}
                                </div>
                            )}
                        />
                    </div>

                    <Separator className="my-6" />

                    <div className="flex justify-center gap-4">
                        <form.AppForm>
                            <form.SubscribeButton label={form.state.isSubmitting ? 'Generating New Idea...' : 'Re-submit for Improvements'} />
                        </form.AppForm>

                        <Button type="button" onClick={handleApprove} disabled={isSaving} className="bg-green-600 hover:bg-green-700">
                            {isSaving ? (
                                <>
                                    <Save className="mr-2 h-4 w-4" />
                                    Saving...
                                </>
                            ) : (
                                <>
                                    <Save className="mr-2 h-4 w-4" />
                                    Approve & Save
                                </>
                            )}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </form>
    );
}

export function PageIdeaWorkshoppingForm({ conversation, apiConnectionStatus, onSuccess, initialPageIdea }: Props) {
    return (
        <div className="mx-auto max-w-4xl space-y-6 p-6">
            <div className="space-y-2 text-center">
                <h1 className="flex items-center justify-center gap-2 text-3xl font-bold">
                    <Lightbulb className="h-8 w-8 text-yellow-500" />
                    Landing Page Idea Reviewer
                </h1>
                <p className="text-gray-600">Review AI-generated landing page ideas and provide feedback</p>
            </div>
            <LandingPageIdea
                conversation={conversation}
                apiConnectionStatus={apiConnectionStatus}
                onSuccess={onSuccess}
                initialPageIdea={initialPageIdea}
            />
        </div>
    );
}
