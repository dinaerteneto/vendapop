import React from 'react';
import { useParams, Link, useNavigate, useOutletContext } from 'react-router-dom';
import { useCart } from '../../context/CartContext';

const Cart: React.FC = () => {
  const { storeSlug } = useParams();
  const navigate = useNavigate();
  const { cart, removeFromCart, updateQuantity, totalValue } = useCart();
  const context = useOutletContext<{ storeInfo: any }>();
  const primaryColor = context?.storeInfo?.primary_color || '#7c3aed';

  return (
    <div>
        <h2 className="text-2xl font-bold mb-4 text-gray-800">Carrinho</h2>
        {cart.length === 0 ? (
            <div className="text-center py-16 bg-white rounded-xl shadow-sm">
                <p className="mb-4 text-gray-500 text-lg">Seu carrinho está vazio.</p>
                <Link 
                    to={`/${storeSlug}`} 
                    className="inline-block px-6 py-3 rounded-full text-white font-bold shadow-md transition-transform hover:scale-105"
                    style={{ backgroundColor: primaryColor }}
                >
                    Voltar para loja
                </Link>
            </div>
        ) : (
            <>
                <div className="bg-white rounded-xl shadow-sm overflow-hidden mb-6 border border-gray-100">
                    {cart.map((item, idx) => (
                        <div key={idx} className="p-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div className="flex items-center gap-4 w-full sm:w-auto">
                                {item.main_image_url ? (
                                    <img src={item.main_image_url} alt={item.name} className="w-16 h-16 object-cover rounded-lg bg-gray-100" />
                                ) : (
                                    <div className="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center text-xs text-gray-400">Sem foto</div>
                                )}
                                
                                <div>
                                    <h3 className="font-bold text-gray-800">{item.name}</h3>
                                    <p className="text-sm text-gray-500">
                                        {item.attributes && Object.keys(item.attributes).length > 0 ? (
                                            <>
                                                {Object.values(item.attributes).join(', ')}
                                            </>
                                        ) : (
                                            <>
                                                {item.size && <span className="mr-2">Tam: {item.size}</span>}
                                                {item.color && <span>Cor: {item.color}</span>}
                                            </>
                                        )}
                                    </p>
                                    <p className="text-sm font-bold text-purple-700 mt-1">
                                        R$ {item.price.toFixed(2).replace('.',',')}
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-center justify-between w-full sm:w-auto gap-6">
                                {/* Quantity Controls */}
                                <div className="flex items-center border border-gray-200 rounded-lg">
                                    <button 
                                        onClick={() => updateQuantity(idx, item.quantity - 1)}
                                        className="px-3 py-1 text-gray-600 hover:bg-gray-50 rounded-l-lg disabled:opacity-50"
                                        disabled={item.quantity <= 1}
                                    >
                                        -
                                    </button>
                                    <span className="px-2 py-1 text-sm font-semibold min-w-[2rem] text-center">{item.quantity}</span>
                                    <button 
                                        onClick={() => updateQuantity(idx, item.quantity + 1)}
                                        className="px-3 py-1 text-gray-600 hover:bg-gray-50 rounded-r-lg"
                                    >
                                        +
                                    </button>
                                </div>

                                <button 
                                    onClick={() => removeFromCart(idx)} 
                                    className="text-red-500 hover:text-red-700 text-sm font-medium transition-colors"
                                >
                                    Remover
                                </button>
                            </div>
                        </div>
                    ))}
                    
                    <div className="p-6 bg-gray-50 flex justify-between items-center">
                        <span className="font-semibold text-gray-600">Total</span>
                        <span className="font-extrabold text-2xl text-gray-900">R$ {totalValue.toFixed(2).replace('.',',')}</span>
                    </div>
                </div>

                <div className="flex flex-col sm:flex-row gap-4">
                    <Link 
                        to={`/${storeSlug}`} 
                        className="flex-1 py-4 text-center border-2 rounded-xl font-bold transition-colors hover:bg-gray-50"
                        style={{ borderColor: primaryColor, color: primaryColor }}
                    >
                        Continuar Comprando
                    </Link>
                    <button 
                        onClick={() => navigate(`/${storeSlug}/checkout`)} 
                        className="flex-1 py-4 text-center text-white rounded-xl font-bold shadow-lg transition-transform hover:scale-[1.02] active:scale-[0.98]"
                        style={{ backgroundColor: '#22c55e', boxShadow: '0 10px 15px -3px rgba(34, 197, 94, 0.3)' }} // Green for checkout
                    >
                        Fechar Pedido
                    </button>
                </div>
            </>
        )}
    </div>
  );
};

export default Cart;
