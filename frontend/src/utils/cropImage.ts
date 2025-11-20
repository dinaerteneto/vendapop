const MAX_WIDTH = 800;
const MAX_HEIGHT = 600;

/**
 * Calcula as dimensões finais respeitando o máximo de 800x600 mantendo a proporção
 */
const calculateFinalDimensions = (width: number, height: number): { width: number; height: number } => {
  // Se já está dentro do limite, retorna as dimensões originais
  if (width <= MAX_WIDTH && height <= MAX_HEIGHT) {
    return { width, height };
  }

  // Calcula a proporção para redimensionar mantendo aspect ratio
  const widthRatio = MAX_WIDTH / width;
  const heightRatio = MAX_HEIGHT / height;
  
  // Usa o menor ratio para garantir que ambas as dimensões fiquem dentro do limite
  const ratio = Math.min(widthRatio, heightRatio);

  return {
    width: Math.round(width * ratio),
    height: Math.round(height * ratio),
  };
};

export const getCroppedImg = (imageSrc: string, pixelCrop: any): Promise<Blob> => {
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

    // Calcula as dimensões finais respeitando o máximo de 800x600
    const finalDimensions = calculateFinalDimensions(pixelCrop.width, pixelCrop.height);
    
    canvas.width = finalDimensions.width;
    canvas.height = finalDimensions.height;

    // Desenha a imagem redimensionada
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

