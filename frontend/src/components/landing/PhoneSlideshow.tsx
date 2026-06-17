const IMAGES = [
  '/images/stores/modachic.png',
  '/images/stores/casa-lar-imoveis.png',
  '/images/stores/techstore-brasil.png',
  '/images/stores/pizzaria-boa-massa.png',
];

const DELAYS = ['0s', '4s', '8s', '12s'];

const PhoneSlideshow: React.FC = () => {
  return (
    <div className="flex justify-center">
      <div className="relative">
        <div className="w-56 h-[400px] md:w-72 md:h-[500px] bg-gray-900 rounded-[3rem] p-2 md:p-3 shadow-2xl">
          <div className="relative w-full h-full bg-white rounded-[2.5rem] overflow-hidden">
            {IMAGES.map((src, i) => (
              <img
                key={src}
                src={src}
                alt={`Loja exemplo ${i + 1}`}
                loading="eager"
                className="absolute inset-0 w-full h-full object-cover object-top motion-safe:animate-phone-slide"
                style={{
                  animationDelay: DELAYS[i],
                  opacity: 0,
                }}
              />
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};

export default PhoneSlideshow;
