import { Editor } from '@/components/blocks/editor-00/editor';
import { useFieldContext } from '@/components/forms/form-context';
import { Checkbox } from '@/components/ui/checkbox';
import { FileInput } from '@/components/ui/file-input';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { TagInput } from '@/components/ui/tag-input';
import { Textarea } from '@/components/ui/textarea';
import { SerializedEditorState } from 'lexical';
import { ComponentProps } from 'react';

// Base text input (already exists)
export function FormInput({ name, label, ...props }: { name: string; label: string } & ComponentProps<typeof Input>) {
    const field = useFieldContext<string>();
    return (
        <div className="grid gap-2">
            <Label htmlFor={name}>{label}</Label>
            <Input id={name} {...props} value={field.state.value} onBlur={field.handleBlur} onChange={(e) => field.handleChange(e.target.value)} />
        </div>
    );
}

// Textarea input
export function FormTextarea({ name, label, ...props }: { name: string; label: string } & ComponentProps<typeof Textarea>) {
    const field = useFieldContext<string>();
    return (
        <div className="grid gap-2">
            <Label htmlFor={name}>{label}</Label>
            <Textarea id={name} {...props} value={field.state.value} onBlur={field.handleBlur} onChange={(e) => field.handleChange(e.target.value)} />
        </div>
    );
}

// Select input
export function FormSelect({
    name,
    label,
    options,
    ...props
}: { name: string; label: string; options: Array<{ value: string; label: string }> } & ComponentProps<typeof Select>) {
    const field = useFieldContext<string>();
    return (
        <div className="grid gap-2">
            <Label htmlFor={name}>{label}</Label>
            <Select {...props} value={field.state.value} onValueChange={field.handleChange}>
                <SelectTrigger id={name}>
                    <SelectValue placeholder="Select an option" />
                </SelectTrigger>
                <SelectContent>
                    {options.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}

// Checkbox input
export function FormCheckbox({ name, label }: { name: string; label: string }) {
    const field = useFieldContext<boolean | string>();
    return (
        <div className="flex items-center space-x-2">
            <Checkbox id={name} checked={!!field.state.value} onCheckedChange={(checked) => field.handleChange(checked)} />
            <Label htmlFor={name}>{label}</Label>
        </div>
    );
}

// Radio group input
export function FormRadioGroup({ name, label, options }: { name: string; label: string; options: Array<{ value: string; label: string }> }) {
    const field = useFieldContext<string>();
    return (
        <div className="grid gap-2">
            <Label>{label}</Label>
            <RadioGroup value={field.state.value} onValueChange={field.handleChange}>
                {options.map((option) => (
                    <div key={option.value} className="flex items-center space-x-2">
                        <RadioGroupItem value={option.value} id={`${name}-${option.value}`} />
                        <Label htmlFor={`${name}-${option.value}`}>{option.label}</Label>
                    </div>
                ))}
            </RadioGroup>
        </div>
    );
}

// Switch input
export function FormSwitch({ name, label }: { name: string; label: string }) {
    const field = useFieldContext<boolean>();
    return (
        <div className="flex items-center space-x-2">
            <Switch id={name} checked={field.state.value} onCheckedChange={field.handleChange} />
            <Label htmlFor={name}>{label}</Label>
        </div>
    );
}

// File input
export function FormFile({ ...props }: { name: string; label: string; filePreview: string | null } & ComponentProps<typeof FileInput>) {
    const field = useFieldContext<File | string | null>();
    const value = field?.state?.value ?? null;
    // @ts-expect-error we'll look deeper into validating the file input later
    return <FileInput value={value} onChange={(files) => field.handleChange(files?.[0] || null)} multiple={false} {...props} />;
}

// Special inputs that use the base FormInput with specific types
export function FormEmail(props: { name: string; label: string }) {
    return <FormInput type="email" {...props} />;
}

export function FormPassword(props: { name: string; label: string }) {
    return <FormInput type="password" {...props} />;
}

export function FormUrl(props: { name: string; label: string }) {
    return <FormInput type="url" {...props} />;
}

export function FormPhone(props: { name: string; label: string }) {
    return <FormInput type="tel" {...props} />;
}

export function FormDate(props: { name: string; label: string }) {
    return <FormInput type="date" {...props} />;
}

export function FormTime(props: { name: string; label: string }) {
    return <FormInput type="time" {...props} />;
}

export function FormRichText({ name, label }: { name: string; label: string }) {
    const field = useFieldContext<SerializedEditorState>();

    return (
        <div className="grid gap-2">
            <Label htmlFor={name}>{label}</Label>
            <div className="min-h-[200px]">
                <Editor editorSerializedState={field.state.value} onSerializedChange={(value) => field.handleChange(value)} />
            </div>
        </div>
    );
}

export function FormTagInput({ name, label }: { name: string; label: string }) {
    const field = useFieldContext<string[]>();
    return (
        <div className="grid gap-2">
            <Label htmlFor={name}>{label}</Label>
            <TagInput value={field.state.value} onChange={(value) => field.handleChange(value)} />
        </div>
    );
}
