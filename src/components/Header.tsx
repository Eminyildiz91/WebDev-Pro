import React, { useState } from 'react';
import { Menu, X, Code, Globe } from 'lucide-react';

const Header = () => {
  const [isMenuOpen, setIsMenuOpen] = useState(false);

  return (
    <header className="fixed w-full top-0 bg-white/95 backdrop-blur-sm border-b border-gray-200 z-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          <div className="flex items-center space-x-2">
            <div className="bg-blue-600 p-2 rounded-lg">
              <Code className="h-6 w-6 text-white" />
            </div>
            <span className="text-xl font-bold text-gray-900">WebDev Pro</span>
          </div>
          
          <nav className="hidden md:flex space-x-8">
            <a href="#home" className="text-gray-700 hover:text-blue-600 transition-colors">Ana Sayfa</a>
            <a href="#services" className="text-gray-700 hover:text-blue-600 transition-colors">Hizmetler</a>
            <a href="#portfolio" className="text-gray-700 hover:text-blue-600 transition-colors">Portföy</a>
            <a href="#about" className="text-gray-700 hover:text-blue-600 transition-colors">Hakkımızda</a>
            <a href="#contact" className="text-gray-700 hover:text-blue-600 transition-colors">İletişim</a>
          </nav>
          
          <div className="hidden md:flex items-center space-x-4">
            <button className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
              Teklif Al
            </button>
          </div>
          
          <button 
            className="md:hidden"
            onClick={() => setIsMenuOpen(!isMenuOpen)}
          >
            {isMenuOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
          </button>
        </div>
        
        {isMenuOpen && (
          <div className="md:hidden">
            <div className="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white border-t border-gray-200">
              <a href="#home" className="block px-3 py-2 text-gray-700 hover:text-blue-600">Ana Sayfa</a>
              <a href="#services" className="block px-3 py-2 text-gray-700 hover:text-blue-600">Hizmetler</a>
              <a href="#portfolio" className="block px-3 py-2 text-gray-700 hover:text-blue-600">Portföy</a>
              <a href="#about" className="block px-3 py-2 text-gray-700 hover:text-blue-600">Hakkımızda</a>
              <a href="#contact" className="block px-3 py-2 text-gray-700 hover:text-blue-600">İletişim</a>
              <button className="w-full mt-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                Teklif Al
              </button>
            </div>
          </div>
        )}
      </div>
    </header>
  );
};

export default Header;