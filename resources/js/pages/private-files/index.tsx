import { PageContainer } from '@/components/page-container';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { NewItemCard } from '@/components/ui/new-item-card';
import AppLayout from '@/layouts/app-layout';
import type { Organisation } from '@/types/models';
import { Head, Link } from '@inertiajs/react';
import { BookOpen, Eye, File, FileAudio, FileText, Video } from 'lucide-react';

interface PrivateFile {
    id: number;
    name: string;
    description: string | null;
    content_type: 'ebook' | 'audiobook' | 'video' | 'document' | 'other';
    file_size: string;
    created_at: string;
    active: boolean;
}

interface PrivateFilesPageProps {
    privateFiles: {
        data: PrivateFile[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    currentOrganisation: Organisation;
}

const contentTypeIcons = {
    ebook: BookOpen,
    audiobook: FileAudio,
    video: Video,
    document: FileText,
    other: File,
};

const contentTypeColors = {
    ebook: 'bg-blue-100 text-blue-800',
    audiobook: 'bg-purple-100 text-purple-800',
    video: 'bg-red-100 text-red-800',
    document: 'bg-green-100 text-green-800',
    other: 'bg-gray-100 text-gray-800',
};

export default function PrivateFilesIndex({ privateFiles, currentOrganisation }: PrivateFilesPageProps) {
    const files = privateFiles.data;

    return (
        <AppLayout>
            <Head title="Private Files" />
            <PageContainer heading={`${currentOrganisation?.name} - Private Files`} subheading="Manage your private media and documents">
                {files?.length === 0 ? (
                    <Card>
                        <CardHeader className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <CardTitle>No private files yet</CardTitle>
                                <CardDescription>Upload your first private file to get started</CardDescription>
                            </div>
                            <Button className="mt-4 w-full sm:mt-0 sm:w-auto" asChild>
                                <Link href={route('private-files.create')}>Upload File</Link>
                            </Button>
                        </CardHeader>
                    </Card>
                ) : (
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {files?.map((file) => {
                            const IconComponent = contentTypeIcons[file.content_type];
                            const colorClasses = contentTypeColors[file.content_type];

                            return (
                                <Card key={file.id}>
                                    <CardHeader>
                                        <div className="flex items-start justify-between">
                                            <div className="flex items-center space-x-3">
                                                <div className={`flex h-10 w-10 items-center justify-center rounded-lg ${colorClasses}`}>
                                                    <IconComponent className="h-5 w-5" />
                                                </div>
                                                <div className="min-w-0 flex-1">
                                                    <CardTitle className="truncate text-base">{file.name}</CardTitle>
                                                    <CardDescription className="text-xs">{file.file_size}</CardDescription>
                                                </div>
                                            </div>
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        {file.description && <p className="line-clamp-2 text-sm text-gray-600">{file.description}</p>}
                                        <p className="mt-2 text-xs text-gray-500">Uploaded {new Date(file.created_at).toLocaleDateString()}</p>
                                    </CardContent>
                                    <CardFooter>
                                        <Button variant="outline" size="sm" asChild className="w-full">
                                            <Link href={route('private-files.show', file.id)}>
                                                <Eye className="mr-2 h-4 w-4" />
                                                View Details
                                            </Link>
                                        </Button>
                                    </CardFooter>
                                </Card>
                            );
                        })}
                        <NewItemCard heading="Upload a new file" href={route('private-files.create')} buttonText="Upload File" />
                    </div>
                )}
            </PageContainer>
        </AppLayout>
    );
}
