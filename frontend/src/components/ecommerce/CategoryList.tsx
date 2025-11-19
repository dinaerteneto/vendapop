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
          <div className={`w-16 h-16 rounded-full flex items-center justify-center mb-2 transition-all border-2 ${selectedCategoryId === null ? 'border-purple-600 bg-purple-50' : 'border-gray-200 bg-gray-100'}`}>
             <span className="text-2xl">✨</span>
          </div>
          <span className={`text-xs text-center font-medium ${selectedCategoryId === null ? 'text-purple-600' : 'text-gray-600'}`}>
            Tudo
          </span>
        </div>

        {categories.map((cat) => (
          <div 
            key={cat.id} 
            onClick={() => onSelectCategory(cat.id)}
            className="flex flex-col items-center min-w-[80px] cursor-pointer group"
          >
            <div className={`w-16 h-16 rounded-full overflow-hidden mb-2 border-2 transition-all ${selectedCategoryId === cat.id ? 'border-purple-600' : 'border-transparent group-hover:border-gray-300'}`}>
              <img 
                src={cat.image_url || `https://ui-avatars.com/api/?name=${cat.name}&background=random&color=fff`} 
                alt={cat.name} 
                className="w-full h-full object-cover"
              />
            </div>
            <span className={`text-xs text-center font-medium ${selectedCategoryId === cat.id ? 'text-purple-600' : 'text-gray-600'}`}>
              {cat.name}
            </span>
          </div>
        ))}
      </div>
    </div>
  );
};

export default CategoryList;
