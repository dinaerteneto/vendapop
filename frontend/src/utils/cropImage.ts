const DEFAULT_WIDTH = 600;
const DEFAULT_HEIGHT = 900;

/**
 * Retorna as dimensões alvo (customizáveis ou padrão 600x900)
 */
const calculateFinalDimensions = (width: number = DEFAULT_WIDTH, height: number = DEFAULT_HEIGHT): { width: number; height: number } => {
  return {
    width,
    height,
  };
};

export const getCroppedImg = (imageSrc: string, pixelCrop: any, targetWidth: number = DEFAULT_WIDTH, targetHeight: number = DEFAULT_HEIGHT): Promise<Blob> => {
  const createImage = (url: string) =>
    new Promise<HTMLImageElement>((resolve, reject) => {
      const image = new Image();
      image.addEventListener('load', () => resolve(image));
      image.addEventListener('error', (error) => reject(error));
      image.setAttribute('crossOrigin', 'anonymous'); // needed to avoid cross-origin issues on CodeSandbox
      image.src = url;
    });

  return new Promise(async (resolve, reject) => {
    const image = await createImage(imageSrc);
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    if (!ctx) {
      return reject(new Error('No 2d context'));
    }

    // Redimensiona para as dimensões alvo especificadas
    const finalDimensions = calculateFinalDimensions(targetWidth, targetHeight);
    
    canvas.width = finalDimensions.width;
    canvas.height = finalDimensions.height;

    // Desenha a imagem redimensionada para as dimensões alvo
    ctx.drawImage(
      image,
      pixelCrop.x,
      pixelCrop.y,
      pixelCrop.width,
      pixelCrop.height,
      0,
      0,
      finalDimensions.width,
      finalDimensions.height
    );

    canvas.toBlob((blob) => {
      if (!blob) {
        // reject(new Error('Canvas is empty'));
        return;
      }
      resolve(blob);
    }, 'image/jpeg', 0.9); // Qualidade 90% para JPEG
  });
};

