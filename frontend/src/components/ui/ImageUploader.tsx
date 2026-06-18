import React, { useState, useRef, useCallback } from 'react';
import api from '../../services/api';
import ImageCropper from './ImageCropper';

interface ImageUploaderProps {
  onImageReady: (file: File) => void;
  currentImageUrl?: string;
  aspectRatio: '2:3' | '1:1' | '16:9';
  label?: string;
  disabled?: boolean;
}

const DIMENSIONS = {
  '2:3':  { width: 600,  height: 900 },
  '1:1':  { width: 400,  height: 400 },
  '16:9': { width: 1200, height: 675 },
} as const;

const ImageUploader: React.FC<ImageUploaderProps> = ({
  onImageReady,
  currentImageUrl,
  aspectRatio,
  label,
  disabled = false,
}) => {
  const [showCropper, setShowCropper] = useState(false);
  const [imageToCrop, setImageToCrop] = useState<string | null>(null);
  const [urlInput, setUrlInput] = useState('');
  const [urlLoading, setUrlLoading] = useState(false);
  const [urlError, setUrlError] = useState<string | null>(null);
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const displayUrl = previewUrl || currentImageUrl;

  const dims = DIMENSIONS[aspectRatio];

  const handleFile = useCallback((file: File) => {
    setUrlError(null);
    setImageToCrop(URL.createObjectURL(file));
    setShowCropper(true);
  }, []);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) handleFile(file);
    e.target.value = '';
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    const file = e.dataTransfer.files?.[0];
    if (file) handleFile(file);
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
  };

  const handleUrlFetch = async () => {
    if (!urlInput.trim()) return;
    setUrlError(null);
    setUrlLoading(true);

    try {
      const response = await api.post('/admin/image-proxy', { url: urlInput });
      setImageToCrop(response.data.url);
      setShowCropper(true);
    } catch (err: any) {
      const message = err?.response?.data?.message || 'Não foi possível carregar a imagem. Verifique o link e tente novamente.';
      setUrlError(message);
    } finally {
      setUrlLoading(false);
    }
  };

  const handleCropComplete = (blob: Blob) => {
    const file = new File([blob], 'image.jpg', { type: 'image/jpeg' });
    const url = URL.createObjectURL(file);
    setPreviewUrl(url);
    onImageReady(file);
    setShowCropper(false);
    setImageToCrop(null);
  };

  const handleCancelCrop = () => {
    setShowCropper(false);
    setImageToCrop(null);
  };

  return (
    <div>
      {label && <label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>}

      <div
        onDrop={handleDrop}
        onDragOver={handleDragOver}
        onClick={() => !disabled && fileInputRef.current?.click()}
        className={`border-2 border-dashed rounded-lg flex flex-col items-center justify-center cursor-pointer overflow-hidden transition-colors ${
          disabled ? 'opacity-50 cursor-not-allowed bg-gray-50' : 'border-gray-300 hover:border-gray-400 bg-gray-50 hover:bg-gray-100'
        }`}
        style={{ minHeight: '8rem' }}
      >
        {displayUrl ? (
          <img
            src={displayUrl}
            alt="Preview"
            className="w-full h-48 object-cover rounded"
          />
        ) : (
          <div className="text-center p-4">
            <svg className="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p className="mt-1 text-sm text-gray-500">Arraste ou clique para escolher</p>
          </div>
        )}
      </div>

      <input
        ref={fileInputRef}
        type="file"
        accept="image/*"
        className="hidden"
        onChange={handleFileChange}
      />

      <div className="flex items-center gap-2 my-2">
        <hr className="flex-1 border-gray-200" />
        <span className="text-xs text-gray-400">ou</span>
        <hr className="flex-1 border-gray-200" />
      </div>

      <div className="flex gap-2">
        <input
          type="url"
          placeholder="Cole o link da imagem"
          value={urlInput}
          onChange={(e) => setUrlInput(e.target.value)}
          disabled={disabled}
          className="flex-1 border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
        />
        <button
          type="button"
          onClick={handleUrlFetch}
          disabled={urlLoading || disabled || !urlInput.trim()}
          className="px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300 disabled:opacity-50 transition"
        >
          {urlLoading ? (
            <svg className="animate-spin h-4 w-4" viewBox="0 0 24 24">
              <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
              <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
          ) : (
            'Usar'
          )}
        </button>
      </div>

      {urlError && <p className="text-xs text-red-500 mt-1">{urlError}</p>}

      {showCropper && imageToCrop && (
        <ImageCropper
          imageSrc={imageToCrop}
          onCropComplete={handleCropComplete}
          onCancel={handleCancelCrop}
          targetWidth={dims.width}
          targetHeight={dims.height}
        />
      )}
    </div>
  );
};

export default ImageUploader;
