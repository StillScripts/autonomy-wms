import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import { PageForm } from '@/pages/websites/pages/page-form';
import type { ContentBlock, ContentBlockType, Website } from '@/types/models';
import { Head } from '@inertiajs/react';

interface PageCreateProps {
    website: Website;
    contentBlocks: ContentBlock[];
    contentBlockTypes: ContentBlockType[];
}

export default function PageCreate({ website, contentBlockTypes, contentBlocks }: PageCreateProps) {
    return (
        <AppLayout>
            <Head title={`Create Page - ${website.title}`} />
            <PageContainer backUrl={route('websites.show', website.id)} heading={`Create Page`} subheading={`Create a new page for ${website.title}`}>
                <PageForm website={website} contentBlockTypes={contentBlockTypes} contentBlocks={contentBlocks} mode="create" />
            </PageContainer>
        </AppLayout>
    );
}
