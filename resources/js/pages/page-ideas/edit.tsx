import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import { type PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import { PageIdeaWorkshoppingForm } from './page-idea-workshopping-form';

interface PageIdea {
    id: number;
    title: string;
    summary: string;
    sections: Array<{
        title: string;
        description: string;
        justification: string;
    }>;
    message: string;
    created_at: string;
    updated_at: string;
    conversation: {
        id: number;
        title: string;
        messages: Array<{
            id: number;
            role: 'user' | 'assistant';
            content: string;
            created_at: string;
        }>;
    };
}

interface Props extends PageProps {
    pageIdea: PageIdea;
    apiConnectionStatus: boolean;
}

export default function Edit({ pageIdea, apiConnectionStatus }: Props) {
    return (
        <AppLayout>
            <Head title={`Edit Page Idea: ${pageIdea.title}`} />
            <PageContainer heading={`Edit Page Idea`} subheading={pageIdea.title} backUrl={route('page-ideas.index')}>
                <PageIdeaWorkshoppingForm
                    conversation={pageIdea.conversation}
                    apiConnectionStatus={apiConnectionStatus}
                    initialPageIdea={{
                        title: pageIdea.title,
                        summary: pageIdea.summary,
                        message: pageIdea.message,
                        sections: pageIdea.sections,
                    }}
                />
            </PageContainer>
        </AppLayout>
    );
}
