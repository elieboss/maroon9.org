/* Staff upload panel on the public Gallery page (gallery.php) */
import { useState } from 'react';
import { Uploader } from './GalleryAdmin.jsx';

export default function GalleryUploader() {
  const [partial, setPartial] = useState(false);

  /* Reload to show the new items; stay put if something failed so staff can read why */
  const onFinished = ({ added, failed }) => {
    if (failed === 0) window.location.reload();
    else setPartial(added > 0);
  };

  return (
    <>
      <Uploader onUploaded={() => {}} onFinished={onFinished} />
      {partial && (
        <button type="button" className="btn btn-maroon btn-sm" onClick={() => window.location.reload()}>Show the files that were added</button>
      )}
    </>
  );
}
