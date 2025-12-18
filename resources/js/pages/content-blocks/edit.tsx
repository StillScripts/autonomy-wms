import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import ContentBlockForm from '@/pages/content-blocks/content-blocks-form';
import { type PageProps } from '@/types';
import type { ContentBlock, ContentBlockType } from '@/types/models';
import { Head } from '@inertiajs/react';

interface Props extends PageProps {
    contentBlock: ContentBlock;
    contentBlockTypes: ContentBlockType[];
}

export default function Edit({ contentBlock, contentBlockTypes }: Props) {
    return (
        <AppLayout>
            <Head title={`Edit Content Block`} />
            <PageContainer heading={`Edit Content Block`} subheading={`Edit a content block`} backUrl={route('content-blocks.index')}>
                <ContentBlockForm contentBlockTypes={contentBlockTypes} contentBlock={contentBlock} mode="edit" />
            </PageContainer>
        </AppLayout>
    );
}
