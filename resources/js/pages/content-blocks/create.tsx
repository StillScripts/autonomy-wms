import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import ContentBlockForm from '@/pages/content-blocks/content-blocks-form';
import { type PageProps } from '@/types';
import type { ContentBlockType } from '@/types/models';
import { Head } from '@inertiajs/react';

interface Props extends PageProps {
    contentBlockTypes: ContentBlockType[];
}

export default function Create({ contentBlockTypes }: Props) {
    return (
        <AppLayout>
            <Head title={`Create Content Block`} />
            <PageContainer heading={`Create Content Block`} subheading={`Create a new content block`} backUrl={route('content-blocks.index')}>
                <ContentBlockForm contentBlockTypes={contentBlockTypes} mode="create" />
            </PageContainer>
        </AppLayout>
    );
}
