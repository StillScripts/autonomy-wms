import { PageContainer } from '@/components/page-container';
import AppLayout from '@/layouts/app-layout';
import { PageForm } from '@/pages/websites/pages/page-form';
import type { ContentBlock, ContentBlockType, Page, Website } from '@/types/models';
import { Head } from '@inertiajs/react';

interface PageEditProps {
    website: Website;
    page: Page;
    pageContentBlocks: { content_block_type_id: string; content_block_id: string }[];
    contentBlocks: ContentBlock[];
    contentBlockTypes: ContentBlockType[];
}

export default function PageEdit({ website, page, contentBlocks, contentBlockTypes, pageContentBlocks }: PageEditProps) {
    return (
        <AppLayout>
            <Head title={`${page.title} - ${website.title}`} />
            <PageContainer backUrl={route('websites.show', website.id)} heading={page.title} subheading={`A page on ${website.title}`}>
                <PageForm
                    website={website}
                    contentBlocks={contentBlocks}
                    contentBlockTypes={contentBlockTypes}
                    defaultValues={{
                        title: page.title,
                        description: page.description ?? '',
                        contentBlocks: pageContentBlocks,
                    }}
                    mode="edit"
                    page={page}
                />
            </PageContainer>
        </AppLayout>
    );
}
