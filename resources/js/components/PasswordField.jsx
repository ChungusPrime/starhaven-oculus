import { useEffect, useState } from 'react';

export default function PasswordField({
    id,
    label,
    prefix,
    value,
    onChange,
    error = '',
    autoComplete,
    minLength,
    placeholder,
    descriptionId,
    visibilityLabel = 'password',
    children,
}) {
    const [visible, setVisible] = useState(false);
    const errorId = `${id}-error`;
    const describedBy = [descriptionId, errorId].filter(Boolean).join(' ');

    useEffect(() => {
        if (value === '') {
            setVisible(false);
        }
    }, [value]);

    return (
        <div className="form-field">
            <label htmlFor={id}>{label}</label>
            <div className={`input-control${error ? ' is-invalid' : ''}`}>
                <span className="input-leading" aria-hidden="true">{prefix}</span>
                <input
                    id={id}
                    name={id}
                    type={visible ? 'text' : 'password'}
                    value={value}
                    minLength={minLength}
                    autoComplete="off"
                    placeholder={placeholder}
                    required
                    aria-describedby={describedBy}
                    aria-invalid={Boolean(error)}
                    onChange={onChange}
                />
                <button
                    className="input-action"
                    type="button"
                    aria-label={`${visible ? 'Hide' : 'Show'} ${visibilityLabel}`}
                    onClick={() => setVisible((current) => !current)}
                >
                    {visible ? 'HIDE' : 'SHOW'}
                </button>
            </div>
            {children}
            <p className="field-error" id={errorId} aria-live="polite">{error}</p>
        </div>
    );
}