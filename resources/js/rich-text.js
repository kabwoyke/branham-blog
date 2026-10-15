// Converts between the rich-text editor's HTML and the Markdown that articles are stored as.

const BLOCK_TAGS = new Set(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'ul', 'ol', 'hr', 'pre', 'figure', 'div', 'section', 'article']);
const ALLOWED_PROTOCOLS = ['http:', 'https:', 'mailto:'];

const escapeText = (text) => text.replace(/([\\`*_[\]])/g, '\\$1');

const wrap = (delimiter, text) => {
    const [, lead, content, trail] = text.match(/^(\s*)([\s\S]*?)(\s*)$/);

    return content === '' ? text : `${lead}${delimiter}${content}${delimiter}${trail}`;
};

const imageMarkdown = (img, caption = '') => {
    const alt = (img.getAttribute('alt') ?? '').replace(/[[\]\r\n]/g, ' ').trim();
    const title = (caption || img.getAttribute('title') || '').replace(/"/g, "'").replace(/[\r\n]+/g, ' ').trim();

    return `![${alt}](${img.getAttribute('src') ?? ''}${title ? ` "${title}"` : ''})`;
};

const inline = (node) => {
    let out = '';

    node.childNodes.forEach((child) => {
        if (child.nodeType === Node.TEXT_NODE) {
            out += escapeText(child.nodeValue.replace(/\s+/g, ' '));

            return;
        }

        if (child.nodeType !== Node.ELEMENT_NODE) {
            return;
        }

        const tag = child.tagName.toLowerCase();

        switch (tag) {
            case 'strong':
            case 'b':
                out += wrap('**', inline(child));
                break;
            case 'em':
            case 'i':
                out += wrap('*', inline(child));
                break;
            case 'code':
                out += `\`${child.textContent}\``;
                break;
            case 'br':
                out += '  \n';
                break;
            case 'img':
                out += imageMarkdown(child);
                break;
            case 'a':
                out += child.getAttribute('href') ? `[${inline(child)}](${child.getAttribute('href')})` : inline(child);
                break;
            default:
                out += inline(child);
        }
    });

    return out;
};

const list = (el) => {
    const ordered = el.tagName.toLowerCase() === 'ol';

    return [...el.children]
        .filter((item) => item.tagName.toLowerCase() === 'li')
        .map((item, index) => {
            const marker = ordered ? `${index + 1}. ` : '- ';
            const indent = ' '.repeat(marker.length);
            const holder = document.createElement('div');
            const nested = [];

            item.childNodes.forEach((child) => {
                const tag = child.nodeType === Node.ELEMENT_NODE ? child.tagName.toLowerCase() : '';

                if (tag === 'ul' || tag === 'ol') {
                    nested.push(list(child));
                } else {
                    holder.appendChild(child.cloneNode(true));
                }
            });

            const lines = [blocks(holder).trim(), ...nested].join('\n').split('\n');

            return marker + lines.map((line, i) => (i === 0 ? line : indent + line)).join('\n');
        })
        .join('\n');
};

const block = (el) => {
    const tag = el.tagName.toLowerCase();

    if (/^h[1-6]$/.test(tag)) {
        return `${'#'.repeat(Number(tag[1]))} ${inline(el).trim()}`;
    }

    switch (tag) {
        case 'p':
            return inline(el).trim();
        case 'blockquote':
            return blocks(el)
                .split('\n')
                .map((line) => (line === '' ? '>' : `> ${line}`))
                .join('\n');
        case 'ul':
        case 'ol':
            return list(el);
        case 'hr':
            return '---';
        case 'pre':
            return `\`\`\`\n${el.textContent.replace(/\n$/, '')}\n\`\`\``;
        case 'figure': {
            const img = el.querySelector('img');

            return img ? imageMarkdown(img, el.querySelector('figcaption')?.textContent ?? '') : blocks(el);
        }
        default:
            return blocks(el);
    }
};

const blocks = (parent) => {
    const parts = [];
    let run = [];

    const flush = () => {
        if (run.length === 0) {
            return;
        }

        const holder = document.createElement('div');
        run.forEach((n) => holder.appendChild(n.cloneNode(true)));
        const text = inline(holder).trim();

        if (text) {
            parts.push(text);
        }

        run = [];
    };

    parent.childNodes.forEach((child) => {
        if (child.nodeType === Node.ELEMENT_NODE && BLOCK_TAGS.has(child.tagName.toLowerCase())) {
            flush();
            const converted = block(child);

            if (converted) {
                parts.push(converted);
            }
        } else {
            run.push(child);
        }
    });

    flush();

    return parts.join('\n\n');
};

export const htmlToMarkdown = (root) => blocks(root).trim();

// Rendered Markdown is only ever shown to the signed-in author, but strip anything executable anyway.
export const sanitizeHtml = (html) => {
    const doc = new DOMParser().parseFromString(html, 'text/html');

    doc.querySelectorAll('script, style, iframe, object, embed, form, link, meta').forEach((el) => el.remove());

    doc.body.querySelectorAll('*').forEach((el) => {
        [...el.attributes].forEach((attr) => {
            const name = attr.name.toLowerCase();

            if (name.startsWith('on') || ((name === 'href' || name === 'src') && !isSafeUrl(attr.value))) {
                el.removeAttribute(attr.name);
            }
        });
    });

    return doc.body.innerHTML;
};

export const isSafeUrl = (value) => {
    try {
        const url = new URL(value, window.location.origin);

        return ALLOWED_PROTOCOLS.includes(url.protocol);
    } catch {
        return false;
    }
};
