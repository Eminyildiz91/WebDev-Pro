import React from 'react';
import { ExternalLink, Github } from 'lucide-react';

const Portfolio = () => {
  const projects = [
    {
      title: "E-Ticaret Platformu",
      description: "Modern ve kullanıcı dostu online mağaza çözümü",
      image: "https://images.pexels.com/photos/4050290/pexels-photo-4050290.jpeg?auto=compress&cs=tinysrgb&w=600",
      tags: ["React", "Node.js", "MongoDB", "Stripe"],
      category: "E-Ticaret"
    },
    {
      title: "Kurumsal Web Sitesi",
      description: "Profesyonel kurumsal kimlik ve modern tasarım",
      image: "https://images.pexels.com/photos/196644/pexels-photo-196644.jpeg?auto=compress&cs=tinysrgb&w=600",
      tags: ["PHP", "MySQL", "Bootstrap", "jQuery"],
      category: "Kurumsal"
    },
    {
      title: "Mobil Uygulama",
      description: "iOS ve Android için cross-platform çözüm",
      image: "https://images.pexels.com/photos/607812/pexels-photo-607812.jpeg?auto=compress&cs=tinysrgb&w=600",
      tags: ["React Native", "Firebase", "Redux"],
      category: "Mobil"
    },
    {
      title: "Dashboard Uygulaması",
      description: "Veri analizi ve yönetim paneli",
      image: "https://images.pexels.com/photos/265087/pexels-photo-265087.jpeg?auto=compress&cs=tinysrgb&w=600",
      tags: ["Vue.js", "Chart.js", "Laravel"],
      category: "Web App"
    },
    {
      title: "Blog Platformu",
      description: "İçerik yönetimi ve sosyal özellikler",
      image: "https://images.pexels.com/photos/261662/pexels-photo-261662.jpeg?auto=compress&cs=tinysrgb&w=600",
      tags: ["WordPress", "Custom Theme", "SEO"],
      category: "CMS"
    },
    {
      title: "Restoran Uygulaması",
      description: "Online sipariş ve rezervasyon sistemi",
      image: "https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg?auto=compress&cs=tinysrgb&w=600",
      tags: ["Flutter", "Express.js", "PostgreSQL"],
      category: "Mobil"
    }
  ];

  return (
    <section id="portfolio" className="py-20 bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-16">
          <h2 className="text-4xl font-bold text-gray-900 mb-4">Portföyümüz</h2>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto">
            Farklı sektörlerden müşterilerimiz için geliştirdiğimiz başarılı projelerimizi keşfedin.
          </p>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {projects.map((project, index) => (
            <div key={index} className="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 group">
              <div className="relative overflow-hidden">
                <img 
                  src={project.image} 
                  alt={project.title}
                  className="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-300"
                />
                <div className="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center space-x-4">
                  <button className="bg-white text-gray-900 p-3 rounded-full hover:bg-gray-100 transition-colors">
                    <ExternalLink className="h-5 w-5" />
                  </button>
                  <button className="bg-white text-gray-900 p-3 rounded-full hover:bg-gray-100 transition-colors">
                    <Github className="h-5 w-5" />
                  </button>
                </div>
                <div className="absolute top-4 left-4">
                  <span className="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                    {project.category}
                  </span>
                </div>
              </div>
              <div className="p-6">
                <h3 className="text-xl font-bold text-gray-900 mb-2">{project.title}</h3>
                <p className="text-gray-600 mb-4 leading-relaxed">{project.description}</p>
                <div className="flex flex-wrap gap-2">
                  {project.tags.map((tag, tagIndex) => (
                    <span key={tagIndex} className="bg-blue-100 text-blue-800 px-2 py-1 rounded-md text-sm">
                      {tag}
                    </span>
                  ))}
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default Portfolio;