import React from 'react';

interface Category {
  id: number;
  name: string;
  slug: string;
  image_url?: string; // Optional for now
}

interface CategoryListProps {
  categories: Category[];
  onSelectCategory: (categoryId: number | null) => void;
  selectedCategoryId: number | null;
}

const CategoryList: React.FC<CategoryListProps> = ({ categories, onSelectCategory, selectedCategoryId }) => {
  return (
    <div className="mb-8">
      <h2 className="text-lg font-bold text-gray-800 mb-4">Categorias</h2>
      <div className="flex gap-4 overflow-x-auto pb-4 scrollbar-hide">
        {/* "All" category */}
        <div 
          onClick={() => onSelectCategory(null)}
          className="flex flex-col items-center min-w-[80px] cursor-pointer"
        >
          <div 
            className={`w-16 h-16 rounded-full flex items-center justify-center mb-2 transition-all border-2 ${selectedCategoryId === null ? 'border-current bg-opacity-10' : 'border-gray-200 bg-gray-100'}`}
            style={{ 
                color: selectedCategoryId === null ? 'var(--theme-primary)' : 'inherit',
                backgroundColor: selectedCategoryId === null ? 'var(--theme-secondary)' : undefined
            }}
          >
             <span className="text-2xl">✨</span>
          </div>
          <span 
            className={`text-xs text-center font-medium`}
            style={{ color: selectedCategoryId === null ? 'var(--theme-primary)' : '#4b5563' }}
          >
            Tudo
          </span>
        </div>

        {categories.map((cat) => (
          <div 
            key={cat.id} 
            onClick={() => onSelectCategory(cat.id)}
            className="flex flex-col items-center min-w-[80px] cursor-pointer group"
          >
            <div 
                className={`w-16 h-16 rounded-full overflow-hidden mb-2 border-2 transition-all flex items-center justify-center bg-gray-100 text-gray-500 text-lg font-bold uppercase ${selectedCategoryId === cat.id ? '' : 'border-transparent group-hover:border-gray-300'}`}
                style={{ borderColor: selectedCategoryId === cat.id ? 'var(--theme-primary)' : undefined }}
            >
              {cat.image_url ? (
                  <img 
                    src={cat.image_url} 
                    alt={cat.name} 
                    className="w-full h-full object-cover"
                    onError={(e) => {
                        e.currentTarget.style.display = 'none';
                        e.currentTarget.parentElement?.classList.add('bg-gray-200');
                        e.currentTarget.parentElement!.innerText = cat.name.substring(0, 2);
                    }}
                  />
              ) : (
                  <span>{cat.name.substring(0, 2)}</span>
              )}
            </div>
            <span 
                className={`text-xs text-center font-medium`}
                style={{ color: selectedCategoryId === cat.id ? 'var(--theme-primary)' : '#4b5563' }}
            >
              {cat.name}
            </span>
          </div>
        ))}
      </div>
    </div>
  );
};

export default CategoryList;
