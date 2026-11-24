/* Staff dashboard "Gallery" tab: upload photos and videos, edit captions, remove items */
import { useEffect, useRef, useState } from 'react';
import { api, postBytes } from '../api.js';
import { Notice, Spinner, formatTimestamp } from './ui.jsx';

const PHOTO_MAX = 25 * 1024 * 1024;
const VIDEO_MAX = 500 * 1024 * 1024;

const sizeLabel = (bytes) => (bytes >= 1024 * 1024 ? `${(bytes / 1024 / 1024).toFixed(1)} MB` : `${Math.ceil(bytes / 1024)} KB`);

/* Problems we can spot before uploading anything */
function precheck(file) {
  if (/\.(heic|heif)$/i.test(file.name)) return 'iPhone HEIC photos can’t be shown in most browsers. Export it as JPEG first.';
  if (file.type.startsWith('image/')) return file.size > PHOTO_MAX ? 'Photos can be up to 25 MB.' : null;
  if (file.type.startsWith('video/')) return file.size > VIDEO_MAX ? 'Videos can be up to 500 MB.' : null;
  return 'Only photos and videos can be added.';
}

/* Uploads one file in pieces; onProgress gets 0..1 */
async function uploadFile(file, caption, onProgress) {
  const { upload, chunk_size: chunk } = await api.post('gallery', 'start', { name: file.name, size: file.size, caption });
  let result = null;
  for (let offset = 0; offset < file.size; offset += chunk) {
    result = await postBytes('gallery', { upload, offset }, file.slice(offset, offset + chunk));
    onProgress(Math.min(1, (offset + chunk) / file.size));
  }
  return result.item;
}

export function Uploader({ onUploaded, onFinished }) {
  const inputRef = useRef(null);
  const [caption, setCaption] = useState('');
  const [queue, setQueue] = useState([]);
  const [dragging, setDragging] = useState(false);
  const busy = queue.some((q) => q.state === 'waiting' || q.state === 'uploading');

  const update = (key, patch) => setQueue((items) => items.map((q) => (q.key === key ? { ...q, ...patch } : q)));

  const start = async (files) => {
    if (busy || files.length === 0) return;
    const items = files.map((file, i) => {
      const problem = precheck(file);
      return { key: `${Date.now()}-${i}`, file, state: problem ? 'error' : 'waiting', progress: 0, message: problem };
    });
    setQueue(items);
    const text = caption.trim();
    let failed = items.filter((q) => q.state === 'error').length;
    for (const item of items.filter((q) => q.state === 'waiting')) {
      update(item.key, { state: 'uploading' });
      try {
        const saved = await uploadFile(item.file, text, (progress) => update(item.key, { progress }));
        update(item.key, { state: 'done', progress: 1 });
        onUploaded(saved);
      } catch (e) {
        failed += 1;
        update(item.key, { state: 'error', message: e.message });
      }
    }
    setCaption('');
    if (inputRef.current) inputRef.current.value = '';
    onFinished?.({ added: items.length - failed, failed });
  };

  const onDrop = (event) => {
    event.preventDefault();
    setDragging(false);
    start([...event.dataTransfer.files]);
  };

  return (
    <div className="panel gallery-uploader">
      <h3 className="panel-title">Add photos &amp; videos</h3>
      <div className="form-group">
        <label htmlFor="gallery-caption">Caption (optional)</label>
        <input
          id="gallery-caption"
          maxLength={300}
          placeholder="e.g. Bone Soup: A Kwanzaa Story, December 2024"
          value={caption}
          onChange={(e) => setCaption(e.target.value)}
          disabled={busy}
        />
        <p className="field-hint">Used for every file in this upload, and read aloud to visitors using screen readers. You can edit each one afterwards.</p>
      </div>
      <label
        className={`dropzone${dragging ? ' dropzone--over' : ''}${busy ? ' dropzone--busy' : ''}`}
        onDragOver={(e) => { e.preventDefault(); setDragging(true); }}
        onDragLeave={() => setDragging(false)}
        onDrop={onDrop}
      >
        <input
          ref={inputRef}
          type="file"
          accept="image/jpeg,image/png,image/webp,image/gif,image/avif,video/mp4,video/webm,video/quicktime"
          multiple
          disabled={busy}
          className="visually-hidden"
          onChange={(e) => start([...e.target.files])}
        />
        <strong>{busy ? 'Uploading…' : 'Choose files or drag them here'}</strong>
        <span>Photos: JPG, PNG, WebP, GIF or AVIF, up to 25 MB. Videos: MP4 (best), WebM or MOV, up to 500 MB.</span>
      </label>

      {queue.length > 0 && (
        <ul className="upload-list" aria-live="polite">
          {queue.map((q) => (
            <li key={q.key} className={`upload-list__item upload-list__item--${q.state}`}>
              <div className="upload-list__row">
                <span className="upload-list__name">{q.file.name}</span>
                <span className="upload-list__status">
                  {q.state === 'waiting' && 'Waiting…'}
                  {q.state === 'uploading' && `${Math.round(q.progress * 100)}% of ${sizeLabel(q.file.size)}`}
                  {q.state === 'done' && 'Added ✓'}
                  {q.state === 'error' && 'Not added'}
                </span>
              </div>
              {q.state === 'uploading' && (
                <progress className="upload-list__bar" max="1" value={q.progress} aria-label={`Uploading ${q.file.name}`} />
              )}
              {q.message && <p className="field-error">{q.message}</p>}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}

function MediaCard({ item, onCaption, onDelete }) {
  const [caption, setCaption] = useState(item.caption);
  const [saving, setSaving] = useState(false);
  const changed = caption.trim() !== item.caption;

  const save = async (event) => {
    event.preventDefault();
    setSaving(true);
    await onCaption(item, caption.trim());
    setSaving(false);
  };

  return (
    <li className="media-card">
      <div className="media-card__preview">
        {item.kind === 'photo' ? (
          <img src={item.thumb} alt="" loading="lazy" />
        ) : (
          <video src={`${item.src}#t=0.1`} poster={item.thumb || undefined} preload="metadata" muted playsInline controls />
        )}
      </div>
      <form className="media-card__body" onSubmit={save}>
        <label className="visually-hidden" htmlFor={`caption-${item.id}`}>Caption</label>
        <input id={`caption-${item.id}`} maxLength={300} placeholder="Add a caption" value={caption} onChange={(e) => setCaption(e.target.value)} />
        <p className="item-list__meta">
          {item.uploaded_by ? `Added by ${item.uploaded_by} · ${formatTimestamp(item.created_at)}` : 'From the original site'}
        </p>
        <div className="media-card__actions">
          {changed && <button type="submit" className="text-btn" disabled={saving}>{saving ? 'Saving…' : 'Save caption'}</button>}
          <button type="button" className="text-btn text-btn--danger" onClick={() => onDelete(item)}>Remove</button>
        </div>
      </form>
    </li>
  );
}

export default function GalleryAdmin() {
  const [items, setItems] = useState(null);
  const [view, setView] = useState('photo');
  const [notice, setNotice] = useState(null);

  useEffect(() => {
    api.get('gallery').then((d) => setItems(d.items), (e) => setNotice({ type: 'error', text: e.message }));
  }, []);

  const onUploaded = (item) => {
    setItems((list) => [item, ...list]);
    setView(item.kind);
  };

  const onCaption = async (item, caption) => {
    try {
      setItems((await api.post('gallery', 'caption', { id: item.id, caption })).items);
      setNotice({ type: 'success', text: 'Caption saved.' });
    } catch (e) {
      setNotice({ type: 'error', text: e.message });
    }
  };

  const onDelete = async (item) => {
    const what = item.kind === 'photo' ? 'photo' : 'video';
    if (!window.confirm(`Remove this ${what} from the Gallery page?`)) return;
    try {
      setItems((await api.post('gallery', 'delete', { id: item.id })).items);
      setNotice({ type: 'success', text: `The ${what} was removed.` });
    } catch (e) {
      setNotice({ type: 'error', text: e.message });
    }
  };

  if (!items) return notice ? <Notice type="error">{notice.text}</Notice> : <Spinner label="Loading gallery…" />;

  const shown = items.filter((i) => i.kind === view);
  const count = (kind) => items.filter((i) => i.kind === kind).length;

  return (
    <>
      <Uploader onUploaded={onUploaded} />
      <Notice type={notice?.type}>{notice?.text}</Notice>
      <div className="toolbar">
        <div className="segmented" role="group" aria-label="Show">
          {[['photo', 'Photos'], ['video', 'Videos']].map(([id, label]) => (
            <button key={id} type="button" aria-pressed={view === id} className={view === id ? 'is-on' : ''} onClick={() => setView(id)}>
              {label} <span className="segmented__count">{count(id)}</span>
            </button>
          ))}
        </div>
        <a className="text-btn" href="gallery.php" target="_blank" rel="noopener">View Gallery page ↗</a>
      </div>
      {shown.length === 0 ? (
        <p className="empty-state">No {view === 'photo' ? 'photos' : 'videos'} yet. Add some above.</p>
      ) : (
        <ul className="media-grid">
          {shown.map((item) => <MediaCard key={item.id} item={item} onCaption={onCaption} onDelete={onDelete} />)}
        </ul>
      )}
    </>
  );
}
