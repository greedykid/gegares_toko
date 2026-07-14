{{--
    Amount breakdown for the order emails.

    A real table, not "**Label:** value" lines: Markdown folds consecutive lines
    into one paragraph, which ran the subtotal, shipping and total together into
    a single unreadable sentence in the delivered mail.

    @param array $rows  [['label' => string, 'value' => string, 'total' => bool?], ...]
--}}
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; border-collapse: collapse; margin: 0 0 4px;">
@foreach ($rows as $row)
    @php $isTotal = $row['total'] ?? false; @endphp
    <tr>
        <td style="padding: {{ $isTotal ? '14px 0 0' : '7px 0' }}; {{ $isTotal ? 'border-top: 1px solid #e2e8f0;' : '' }} text-align: left; font-size: {{ $isTotal ? '15px' : '14px' }}; font-weight: {{ $isTotal ? '600' : '400' }}; color: {{ $isTotal ? '#1a202c' : '#718096' }};">
            {{ $row['label'] }}
        </td>
        <td style="padding: {{ $isTotal ? '14px 0 0' : '7px 0' }}; {{ $isTotal ? 'border-top: 1px solid #e2e8f0;' : '' }} text-align: right; font-size: {{ $isTotal ? '18px' : '14px' }}; font-weight: {{ $isTotal ? '700' : '600' }}; color: {{ $isTotal ? '#276749' : '#2d3748' }}; white-space: nowrap;">
            {{ $row['value'] }}
        </td>
    </tr>
@endforeach
</table>
