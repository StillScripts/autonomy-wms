import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import PageIdeasForm from '@/pages/page-ideas/page-ideas-form';
import { Head } from '@inertiajs/react';

interface Conversation {
    id: number;
    title: string;
    messages: Array<{
        id: number;
        content: string;
        created_at: string;
    }>;
}

interface PageIdea {
    id: number;
    title: string;
    summary: string;
    created_at: string;
    updated_at: string;
    version_number: number;
    is_latest_version: boolean;
}

interface Props {
    conversation: Conversation;
    latestPageIdea: PageIdea | null;
    pageIdeaVersions: PageIdea[];
    apiConnectionStatus: boolean;
}

export default function Create({ conversation, apiConnectionStatus }: Props) {
    return (
        <AppLayout>
            <Head title={`Create Page Idea`} />
            <PageContainer heading={`Create Page Idea`} subheading={`Create a new landing page idea using AI`} backUrl={route('page-ideas.index')}>
                <PageIdeasForm conversation={conversation} apiConnectionStatus={apiConnectionStatus} />
            </PageContainer>
        </AppLayout>
    );
}
