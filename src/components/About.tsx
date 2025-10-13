import React from 'react';
import { Users, Award, Clock, Target } from 'lucide-react';

const About = () => {
  const stats = [
    { icon: <Users className="h-8 w-8" />, value: "50+", label: "Mutlu Müşteri" },
    { icon: <Award className="h-8 w-8" />, value: "150+", label: "Tamamlanan Proje" },
    { icon: <Clock className="h-8 w-8" />, value: "5", label: "Yıl Tecrübe" },
    { icon: <Target className="h-8 w-8" />, value: "100%", label: "Başarı Oranı" }
  ];

  return (
    <section id="about" className="py-20 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div>
            <h2 className="text-4xl font-bold text-gray-900 mb-6">Hakkımızda</h2>
            <p className="text-lg text-gray-600 mb-6 leading-relaxed">
              WebDev Pro olarak, 2019 yılından bu yana dijital dünyada markaları 
              başarıya ulaştırmak için çalışıyoruz. Uzman ekibimiz, en son teknolojileri 
              kullanarak modern, kullanıcı dostu ve performanslı web çözümleri geliştirmektedir.
            </p>
            <p className="text-lg text-gray-600 mb-8 leading-relaxed">
              Müşteri memnuniyetini ön planda tutarak, her projeye özel yaklaşım sergiliyoruz. 
              Kurumsal kimliğinizi dijital ortamda en iyi şekilde temsil edecek çözümler 
              sunmak için buradayız.
            </p>
            
            <div className="grid grid-cols-2 gap-6">
              {stats.map((stat, index) => (
                <div key={index} className="text-center p-6 bg-gray-50 rounded-xl hover:bg-blue-50 transition-colors">
                  <div className="text-blue-600 mb-2 flex justify-center">{stat.icon}</div>
                  <div className="text-3xl font-bold text-gray-900 mb-1">{stat.value}</div>
                  <div className="text-sm text-gray-600">{stat.label}</div>
                </div>
              ))}
            </div>
          </div>
          
          <div className="relative">
            <div className="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 text-white">
              <h3 className="text-2xl font-bold mb-4">Neden Bizi Seçmelisiniz?</h3>
              <ul className="space-y-4">
                <li className="flex items-start space-x-3">
                  <div className="w-2 h-2 bg-white rounded-full mt-2 flex-shrink-0"></div>
                  <span>Modern teknolojiler ve en iyi yazılım pratikleri</span>
                </li>
                <li className="flex items-start space-x-3">
                  <div className="w-2 h-2 bg-white rounded-full mt-2 flex-shrink-0"></div>
                  <span>Zamanında teslimat ve profesyonel destek</span>
                </li>
                <li className="flex items-start space-x-3">
                  <div className="w-2 h-2 bg-white rounded-full mt-2 flex-shrink-0"></div>
                  <span>SEO optimizasyonu ve performans odaklı geliştirme</span>
                </li>
                <li className="flex items-start space-x-3">
                  <div className="w-2 h-2 bg-white rounded-full mt-2 flex-shrink-0"></div>
                  <span>Sürekli bakım ve güncelleme desteği</span>
                </li>
              </ul>
            </div>
            
            <div className="absolute -top-4 -right-4 bg-yellow-400 text-black p-4 rounded-full shadow-lg">
              <span className="font-bold">5★</span>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default About;