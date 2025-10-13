import React from 'react';
import { Globe, Smartphone, ShoppingCart, Search, Zap, Shield } from 'lucide-react';

const Services = () => {
  const services = [
    {
      icon: <Globe className="h-8 w-8" />,
      title: "Kurumsal Web Sitesi",
      description: "Markanızı en iyi şekilde yansıtan, profesyonel ve modern web siteleri geliştiriyoruz.",
      features: ["Responsive Tasarım", "SEO Optimizasyonu", "Hızlı Yükleme"]
    },
    {
      icon: <ShoppingCart className="h-8 w-8" />,
      title: "E-Ticaret Çözümleri",
      description: "Satışlarınızı artıracak, güvenli ve kullanıcı dostu e-ticaret platformları.",
      features: ["Ödeme Entegrasyonu", "Stok Yönetimi", "Mobil Uyumlu"]
    },
    {
      icon: <Smartphone className="h-8 w-8" />,
      title: "Mobil Uygulama",
      description: "iOS ve Android için native ve hibrit mobil uygulamalar geliştiriyoruz.",
      features: ["Cross-Platform", "Push Notification", "Offline Destek"]
    },
    {
      icon: <Search className="h-8 w-8" />,
      title: "SEO Optimizasyonu",
      description: "Google'da üst sıralarda yer almanızı sağlayacak SEO stratejileri uyguluyoruz.",
      features: ["Anahtar Kelime Analizi", "Teknik SEO", "İçerik Optimizasyonu"]
    },
    {
      icon: <Zap className="h-8 w-8" />,
      title: "Performans Optimizasyonu",
      description: "Web sitenizin hızını artırıp, kullanıcı deneyimini iyileştiriyoruz.",
      features: ["Hız Optimizasyonu", "Kod Minifikasyonu", "CDN Entegrasyonu"]
    },
    {
      icon: <Shield className="h-8 w-8" />,
      title: "Güvenlik & Bakım",
      description: "Web sitenizin güvenliğini sağlıyor ve düzenli bakımını yapıyoruz.",
      features: ["SSL Sertifikası", "Düzenli Backup", "Güvenlik Güncellemeleri"]
    }
  ];

  return (
    <section id="services" className="py-20 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-16">
          <h2 className="text-4xl font-bold text-gray-900 mb-4">Hizmetlerimiz</h2>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto">
            Dijital dünyadaki tüm ihtiyaçlarınız için kapsamlı çözümler sunuyoruz. 
            Modern teknolojiler ve uzman ekibimizle yanınızdayız.
          </p>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {services.map((service, index) => (
            <div key={index} className="bg-gray-50 rounded-2xl p-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 group">
              <div className="bg-blue-600 text-white p-3 rounded-lg inline-block mb-6 group-hover:scale-110 transition-transform">
                {service.icon}
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-4">{service.title}</h3>
              <p className="text-gray-600 mb-6 leading-relaxed">{service.description}</p>
              <ul className="space-y-2">
                {service.features.map((feature, featureIndex) => (
                  <li key={featureIndex} className="flex items-center text-sm text-gray-700">
                    <div className="w-2 h-2 bg-blue-600 rounded-full mr-3"></div>
                    {feature}
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default Services;