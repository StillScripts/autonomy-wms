import { ContentEditable } from '@/components/editor/editor-ui/content-editable';

import { $createListNode, $isListNode, ListType } from '@lexical/list';
import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext';
import { LexicalErrorBoundary } from '@lexical/react/LexicalErrorBoundary';
import { ListPlugin } from '@lexical/react/LexicalListPlugin';
import { RichTextPlugin } from '@lexical/react/LexicalRichTextPlugin';
import { $createHeadingNode, HeadingTagType } from '@lexical/rich-text';
import { $setBlocksType } from '@lexical/selection';
import { $createParagraphNode, $getSelection, $isRangeSelection } from 'lexical';

import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Heading1, Heading2, Heading3, Heading4, Heading5, Heading6, List, ListOrdered, Type } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

export function SelectDemo() {
    return (
        <Select>
            <SelectTrigger className="w-[180px]">
                <SelectValue placeholder="Select a fruit" />
            </SelectTrigger>
            <SelectContent>
                <SelectGroup>
                    <SelectLabel>Fruits</SelectLabel>
                    <SelectItem value="apple">Apple</SelectItem>
                    <SelectItem value="banana">Banana</SelectItem>
                    <SelectItem value="blueberry">Blueberry</SelectItem>
                    <SelectItem value="grapes">Grapes</SelectItem>
                    <SelectItem value="pineapple">Pineapple</SelectItem>
                </SelectGroup>
            </SelectContent>
        </Select>
    );
}

function Toolbar() {
    const [editor] = useLexicalComposerContext();
    const [blockType, setBlockType] = useState<string>('p');

    const formatHeading = (headingSize: number) => {
        editor.update(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                $setBlocksType(selection, () => $createHeadingNode(`h${headingSize}` as HeadingTagType));
            }
        });
    };

    const formatParagraph = () => {
        editor.update(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                $setBlocksType(selection, () => $createParagraphNode());
            }
        });
    };

    const formatList = (listType: ListType) => {
        editor.update(() => {
            const selection = $getSelection();
            if ($isRangeSelection(selection)) {
                $setBlocksType(selection, () => $createListNode(listType));
            }
        });
    };

    const updateToolbar = useCallback(() => {
        editor.getEditorState().read(() => {
            const selection = $getSelection();
            if (!$isRangeSelection(selection)) return;

            const anchorNode = selection.anchor.getNode();
            const element = anchorNode.getKey() === 'root' ? anchorNode : anchorNode.getTopLevelElement();

            if (element === null) return;

            const elementKey = element.getKey();
            const elementDOM = editor.getElementByKey(elementKey);

            if (elementDOM === null) return;

            // Check for paragraph
            if (element.getType() === 'paragraph') {
                setBlockType('p');
                return;
            }

            // Check for headings
            if (element.getType() === 'heading') {
                const tag = elementDOM.tagName.toLowerCase();
                setBlockType(tag);
                return;
            }

            // Check for lists
            if ($isListNode(element)) {
                const parentList = elementDOM.tagName.toLowerCase();
                setBlockType(parentList === 'ul' ? 'ul' : 'ol');
                return;
            }
        });
    }, [editor]);

    useEffect(() => {
        editor.registerUpdateListener(({ editorState }) => {
            editorState.read(() => {
                updateToolbar();
            });
        });
    }, [editor, updateToolbar]);

    const getFormatLabel = (type: string) => {
        const formats = {
            p: 'Paragraph',
            h1: 'Heading 1',
            h2: 'Heading 2',
            h3: 'Heading 3',
            h4: 'Heading 4',
            h5: 'Heading 5',
            h6: 'Heading 6',
            ul: 'Bullet List',
            ol: 'Numbered List',
        };
        return formats[type as keyof typeof formats] || 'Paragraph';
    };

    const getFormatIcon = (type: string) => {
        const icons = {
            p: <Type className="mr-2 h-4 w-4" />,
            h1: <Heading1 className="mr-2 h-4 w-4" />,
            h2: <Heading2 className="mr-2 h-4 w-4" />,
            h3: <Heading3 className="mr-2 h-4 w-4" />,
            h4: <Heading4 className="mr-2 h-4 w-4" />,
            h5: <Heading5 className="mr-2 h-4 w-4" />,
            h6: <Heading6 className="mr-2 h-4 w-4" />,
            ul: <List className="mr-2 h-4 w-4" />,
            ol: <ListOrdered className="mr-2 h-4 w-4" />,
        };
        return icons[type as keyof typeof icons] || <Type className="mr-2 h-4 w-4" />;
    };

    return (
        <div className="flex gap-2 border-b p-2">
            <Select
                value={blockType}
                onValueChange={(value) => {
                    if (value.startsWith('h')) {
                        formatHeading(parseInt(value[1]));
                    } else if (value === 'p') {
                        formatParagraph();
                    } else if (value === 'ul' || value === 'ol') {
                        formatList(value === 'ul' ? 'bullet' : 'number');
                    }
                }}
            >
                <SelectTrigger className="w-[180px]">
                    <SelectValue>
                        {getFormatIcon(blockType)}
                        {getFormatLabel(blockType)}
                    </SelectValue>
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="p">
                        <Type className="mr-2 h-4 w-4" />
                        Paragraph
                    </SelectItem>
                    <SelectItem value="h1">
                        <Heading1 className="mr-2 h-4 w-4" />
                        Heading 1
                    </SelectItem>
                    <SelectItem value="h2">
                        <Heading2 className="mr-2 h-4 w-4" />
                        Heading 2
                    </SelectItem>
                    <SelectItem value="h3">
                        <Heading3 className="mr-2 h-4 w-4" />
                        Heading 3
                    </SelectItem>
                    <SelectItem value="h4">
                        <Heading4 className="mr-2 h-4 w-4" />
                        Heading 4
                    </SelectItem>
                    <SelectItem value="h5">
                        <Heading5 className="mr-2 h-4 w-4" />
                        Heading 5
                    </SelectItem>
                    <SelectItem value="h6">
                        <Heading6 className="mr-2 h-4 w-4" />
                        Heading 6
                    </SelectItem>
                    <SelectItem value="ul">
                        <List className="mr-2 h-4 w-4" />
                        Bullet List
                    </SelectItem>
                    <SelectItem value="ol">
                        <ListOrdered className="mr-2 h-4 w-4" />
                        Numbered List
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>
    );
}

export function Plugins() {
    return (
        <div className="relative">
            <div className="relative">
                <Toolbar />
                <RichTextPlugin
                    contentEditable={
                        <div className="">
                            <div className="">
                                <ContentEditable placeholder={'Start typing ...'} />
                            </div>
                        </div>
                    }
                    ErrorBoundary={LexicalErrorBoundary}
                />
                <ListPlugin />
            </div>
        </div>
    );
}
