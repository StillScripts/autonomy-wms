import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { FileInput } from '@/components/ui/file-input';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';

const fieldTypes = [
        {
            name: 'Text Input',
            fieldType: 'text',
            description: 'Single line text input field',
            example: <Input type="text" placeholder="Enter text here" />,
        },
        {
            name: 'Text Area',
            fieldType: 'textarea',
            description: 'Multi-line text input field',
            example: <Textarea placeholder="Enter longer text here" rows={3} />,
        },
        {
            name: 'Rich Text',
            fieldType: 'richtext',
            description: 'Rich text input field',
            example: <Textarea placeholder="Enter longer text here" rows={3} />,
        },

        {
            name: 'Number',
            fieldType: 'number',
            description: 'Numeric input field',
            example: <Input type="number" placeholder="0" />,
        },
        {
            name: 'Select / Dropdown',
            fieldType: 'select',
            description: 'Dropdown selection field',
            example: (
                <Select>
                    <SelectTrigger>
                        <SelectValue placeholder="Select an option" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="option1">Option 1</SelectItem>
                        <SelectItem value="option2">Option 2</SelectItem>
                        <SelectItem value="option3">Option 3</SelectItem>
                    </SelectContent>
                </Select>
            ),
        },
        {
            name: 'Checkbox',
            fieldType: 'checkbox',
            description: 'Single checkbox or multiple checkbox group',
            example: (
                <div className="flex items-center space-x-2">
                    <Checkbox id="checkbox" />
                    <Label htmlFor="checkbox">Checkbox option</Label>
                </div>
            ),
        },
        {
            name: 'Radio Group',
            fieldType: 'radio',
            description: 'Group of radio button options',
            example: (
                <RadioGroup defaultValue="option1">
                    <div className="flex items-center space-x-2">
                        <RadioGroupItem value="option1" id="option1" />
                        <Label htmlFor="option1">Option 1</Label>
                    </div>
                    <div className="flex items-center space-x-2">
                        <RadioGroupItem value="option2" id="option2" />
                        <Label htmlFor="option2">Option 2</Label>
                    </div>
                </RadioGroup>
            ),
        },
        {
            name: 'Switch / Toggle',
            fieldType: 'switch',
            description: 'Toggle switch for boolean values',
            example: (
                <div className="flex items-center space-x-2">
                    <Switch id="switch" />
                    <Label htmlFor="switch">Toggle option</Label>
                </div>
            ),
        },
        {
            name: 'Date Picker',
            fieldType: 'date',
            description: 'Date selection field',
            example: (
                <div className="relative">
                    <Input type="date" />
                </div>
            ),
        },
        {
            name: 'Time Picker',
            fieldType: 'time',
            description: 'Time selection field',
            example: <Input type="time" />,
        },
        {
            name: 'File Upload',
            fieldType: 'file',
            description: 'File upload field',
            example: (
                <FileInput />
            ),
        },
        {
            name: 'Email',
            fieldType: 'email',
            description: 'Email input field with validation',
            example: <Input type="email" placeholder="email@example.com" />,
        },
        {
            name: 'Password',
            fieldType: 'password',
            description: 'Password input field with masking',
            example: <Input type="password" placeholder="••••••••" />,
        },
        {
            name: 'URL',
            fieldType: 'url',
            description: 'URL input field with validation',
            example: <Input type="url" placeholder="https://example.com" />,
        },
        {
            name: 'Phone',
            fieldType: 'tel',
            description: 'Telephone number input field',
            example: <Input type="tel" placeholder="+1 (555) 000-0000" />,
        },
    ] 
export const allFieldTypes: Array<typeof fieldTypes[number]['fieldType']> = [
    ...fieldTypes.map((field) => field.fieldType),
] as const;

export default function FormFieldTypes() {
    return (
        <div className="container mx-auto py-10">
            <Card>
                <CardHeader>
                    <CardTitle>Form Field Types</CardTitle>
                    <CardDescription>Common input field types that can be used in a dynamic form builder</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Field Name</TableHead>
                                <TableHead>fieldType</TableHead>
                                <TableHead>Description</TableHead>
                                <TableHead>Example</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {fieldTypes.map((field) => (
                                <TableRow key={field.fieldType}>
                                    <TableCell className="font-medium">{field.name}</TableCell>
                                    <TableCell>
                                        <code className="bg-muted relative rounded px-[0.3rem] py-[0.2rem] font-mono text-sm">{field.fieldType}</code>
                                    </TableCell>
                                    <TableCell>{field.description}</TableCell>
                                    <TableCell className="min-w-[200px]">{field.example}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    );
}
