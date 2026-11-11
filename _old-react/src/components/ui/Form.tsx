import { useId, type InputHTMLAttributes, type ReactNode, type SelectHTMLAttributes, type TextareaHTMLAttributes } from 'react';
import { Icon } from './Icon';

interface FieldShellProps {
  id: string;
  label?: string;
  hideLabel?: boolean;
  error?: ReactNode;
  children: ReactNode;
}

function FieldShell({ id, label, hideLabel, error, children }: FieldShellProps) {
  return (
    <div className="field">
      {label && (
        <label htmlFor={id} className={hideLabel ? 'sr-only' : 'field__label'}>
          {label}
        </label>
      )}
      {children}
      {error && (
        <div className="field__error" id={`${id}-err`}>
          <Icon name="alert" size={16} color="var(--c-danger)" />
          <p>{error}</p>
        </div>
      )}
    </div>
  );
}

type TextFieldProps = InputHTMLAttributes<HTMLInputElement> & {
  label?: string;
  hideLabel?: boolean;
  error?: ReactNode;
};

export function TextField({ label, hideLabel, error, id, className, ...rest }: TextFieldProps) {
  const auto = useId();
  const fid = id ?? auto;
  return (
    <FieldShell id={fid} label={label} hideLabel={hideLabel} error={error}>
      <input
        id={fid}
        type="text"
        className={['input', error ? 'input--error' : '', className ?? ''].join(' ').trim()}
        aria-invalid={error ? true : undefined}
        aria-describedby={error ? `${fid}-err` : undefined}
        {...rest}
      />
    </FieldShell>
  );
}

type TextAreaProps = TextareaHTMLAttributes<HTMLTextAreaElement> & { label?: string; error?: ReactNode };

export function TextAreaField({ label, error, id, rows = 4, ...rest }: TextAreaProps) {
  const auto = useId();
  const fid = id ?? auto;
  return (
    <FieldShell id={fid} label={label} error={error}>
      <textarea id={fid} rows={rows} className="textarea" {...rest} />
    </FieldShell>
  );
}

type SelectFieldProps = SelectHTMLAttributes<HTMLSelectElement> & {
  label?: string;
  hideLabel?: boolean;
  options: (string | { value: string; label: string })[];
};

export function SelectField({ label, hideLabel, options, id, ...rest }: SelectFieldProps) {
  const auto = useId();
  const fid = id ?? auto;
  return (
    <FieldShell id={fid} label={label} hideLabel={hideLabel}>
      <div className="select-wrap">
        <select id={fid} className="select" {...rest}>
          {options.map((o) => {
            const opt = typeof o === 'string' ? { value: o, label: o } : o;
            return (
              <option key={opt.value} value={opt.value}>
                {opt.label}
              </option>
            );
          })}
        </select>
        <span className="select-wrap__icon">
          <Icon name="chevronDown" size={18} color="var(--c-muted)" />
        </span>
      </div>
    </FieldShell>
  );
}

interface SwitchRowProps {
  title: string;
  hint?: string;
  checked: boolean;
  onChange: (v: boolean) => void;
}

export function SwitchRow({ title, hint, checked, onChange }: SwitchRowProps) {
  return (
    <div className="row gap-12">
      <div className="grow">
        <p className="t-small t-strong">{title}</p>
        {hint && <p className="t-small t-muted">{hint}</p>}
      </div>
      <button
        type="button"
        role="switch"
        aria-checked={checked}
        aria-label={title}
        className="switch"
        onClick={() => onChange(!checked)}
      />
    </div>
  );
}

interface DropzoneProps {
  title: string;
  hint?: string;
  iconSize?: number;
  onFiles?: (files: File[]) => void;
  accept?: string;
  multiple?: boolean;
  compact?: boolean;
}

export function Dropzone({ title, hint, iconSize = 22, onFiles, accept, multiple, compact }: DropzoneProps) {
  return (
    <label className="dropzone" style={compact ? { padding: 18, borderRadius: 12 } : undefined}>
      <Icon name="upload" size={iconSize} color="var(--c-primary)" />
      <p className="t-small t-strong t-primary">{title}</p>
      {hint && <p className="t-small t-muted">{hint}</p>}
      <input
        type="file"
        accept={accept}
        multiple={multiple}
        onChange={(e) => {
          onFiles?.(Array.from(e.target.files ?? []));
          e.target.value = '';
        }}
      />
    </label>
  );
}

export function formatSize(bytes: number) {
  if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`;
  return `${(bytes / 1024 / 1024).toFixed(1).replace('.', ',')} MB`;
}
