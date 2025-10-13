import React from 'react';
import { ArrowRight, Play } from 'lucide-react';

const Hero = () => {
  return (
    <section id="home" className="pt-16 bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div>
            <h1 className="text-4xl md:text-6xl font-bold text-gray-900 leading-tight">
              Modern Web 
              <span className="text-blue-600"> Çözümleri</span>
            </h1>
            <p className="text-xl text-gray-600 mt-6 leading-relaxed">
              Markanızı dijital dünyada öne çıkaracak, kullanıcı odaklı ve modern web siteleri geliştiriyoruz. 
              En son teknolojilerle, hızlı ve güvenli çözümler sunuyoruz.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 mt-8">
              <button className="bg-blue-600 text-white px-8 py-4 rounded-lg font-medium hover:bg-blue-700 transition-all duration-300 transform hover:scale-105 flex items-center justify-center space-x-2">
                <span>Projeni Başlat</span>
                <ArrowRight className="h-5 w-5" />
              </button>
              <button className="border border-gray-300 text-gray-700 px-8 py-4 rounded-lg font-medium hover:bg-gray-50 transition-all duration-300 flex items-center justify-center space-x-2">
                <Play className="h-5 w-5" />
                <span>Nasıl Çalışır?</span>
              </button>
            </div>
            <div className="grid grid-cols-3 gap-8 mt-12">
              <div className="text-center">
                <div className="text-3xl font-bold text-blue-600">150+</div>
                <div className="text-sm text-gray-600">Tamamlanan Proje</div>
              </div>
              <div className="text-center">
                <div className="text-3xl font-bold text-blue-600">50+</div>
                <div className="text-sm text-gray-600">Mutlu Müşteri</div>
              </div>
              <div className="text-center">
                <div className="text-3xl font-bold text-blue-600">5</div>
                <div className="text-sm text-gray-600">Yıl Tecrübe</div>
              </div>
            </div>
          </div>
          <div className="relative">
            <div className="bg-white rounded-2xl shadow-2xl p-8 transform rotate-3 hover:rotate-0 transition-transform duration-300">
              <div className="bg-gray-100 rounded-lg p-6">
                <div className="flex items-center space-x-2 mb-4">
                  <div className="w-3 h-3 bg-red-500 rounded-full"></div>
                  <div className="w-3 h-3 bg-yellow-500 rounded-full"></div>
                  <div className="w-3 h-3 bg-green-500 rounded-full"></div>
                </div>
                <div className="space-y-3">
                  <div className="h-4 bg-blue-200 rounded w-3/4"></div>
                  <div className="h-4 bg-gray-200 rounded w-1/2"></div>
                  <div className="h-4 bg-blue-200 rounded w-2/3"></div>
                  <div className="h-20 bg-gradient-to-r from-blue-100 to-purple-100 rounded-lg"></div>
                </div>
              </div>
            </div>
            <div className="absolute -top-4 -right-4 bg-blue-600 text-white p-4 rounded-full shadow-lg">
              <span className="text-sm font-medium">Responsive</span>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Hero;