import React from 'react';

interface SortableHeaderProps {
  label: string;
  sortKey: string;
  currentSort: string;
  currentDirection: 'asc' | 'desc';
  onSort: (key: string, direction: 'asc' | 'desc') => void;
  className?: string;
}

const SortableHeader: React.FC<SortableHeaderProps> = ({
  label,
  sortKey,
  currentSort,
  currentDirection,
  onSort,
  className = '',
}) => {
  const isActive = currentSort === sortKey;
  const handleClick = () => {
    if (isActive) {
      // Se já está ordenado por esta coluna, inverte a direção
      onSort(sortKey, currentDirection === 'asc' ? 'desc' : 'asc');
    } else {
      // Se não está ordenado, ordena ascendente
      onSort(sortKey, 'asc');
    }
  };

  return (
    <th
      className={`px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 select-none ${className}`}
      onClick={handleClick}
    >
      <div className="flex items-center gap-2">
        <span>{label}</span>
        <div className="flex flex-col">
          {isActive ? (
            currentDirection === 'asc' ? (
              <svg className="w-3 h-3 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clipRule="evenodd" />
              </svg>
            ) : (
              <svg className="w-3 h-3 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
              </svg>
            )
          ) : (
            <div className="flex flex-col -space-y-1">
              <svg className="w-2 h-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clipRule="evenodd" />
              </svg>
              <svg className="w-2 h-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
              </svg>
            </div>
          )}
        </div>
      </div>
    </th>
  );
};

export default SortableHeader;

