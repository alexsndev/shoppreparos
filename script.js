function iniciarCarrossel(selector) {
    const carrossel = document.querySelector(selector);
    if (!carrossel) return;

    const slidesContainer = carrossel.querySelector('.slides');
    const slides = carrossel.querySelectorAll('.slide');
    const indicadoresContainer = carrossel.querySelector('.indicadores');
    const setaDireita = carrossel.querySelector('.seta.direita');
    const setaEsquerda = carrossel.querySelector('.seta.esquerda');

    let indiceAtual = 0;
    let autoplayInterval;

    // Cria os indicadores dinamicamente
    indicadoresContainer.innerHTML = '';
    slides.forEach((_, index) => {
        const bolinha = document.createElement('span');
        bolinha.classList.add('bolinha');
        if (index === 0) bolinha.classList.add('ativo');
        bolinha.addEventListener('click', () => {
            pausarAutoplay();
            irParaSlide(index);
        });
        indicadoresContainer.appendChild(bolinha);
    });

    const bolinhas = indicadoresContainer.querySelectorAll('.bolinha');

    function mostrarSlide(indice) {
        const larguraSlide = slides[0].clientWidth;
        slidesContainer.style.transform = `translateX(-${indice * larguraSlide}px)`;

        bolinhas.forEach(b => b.classList.remove('ativo'));
        bolinhas[indice].classList.add('ativo');
    }

    function irParaSlide(indice) {
        if (indice < 0) {
            indiceAtual = slides.length - 1;
        } else if (indice >= slides.length) {
            indiceAtual = 0;
        } else {
            indiceAtual = indice;
        }
        mostrarSlide(indiceAtual);
    }

    // Navegação manual com pausa do autoplay
    setaDireita.addEventListener('click', () => {
        pausarAutoplay();
        irParaSlide(indiceAtual + 1);
    });

    setaEsquerda.addEventListener('click', () => {
        pausarAutoplay();
        irParaSlide(indiceAtual - 1);
    });

    window.addEventListener('resize', () => {
        mostrarSlide(indiceAtual);
    });

    // Autoplay
    function iniciarAutoplay() {
        autoplayInterval = setInterval(() => {
            irParaSlide(indiceAtual + 1);
        }, 3000); // 3 segundos
    }

    function pausarAutoplay() {
        clearInterval(autoplayInterval);
    }

    // Pausar ao passar o mouse (desktop)
    carrossel.addEventListener('mouseenter', pausarAutoplay);
    carrossel.addEventListener('mouseleave', iniciarAutoplay);

    iniciarAutoplay();
    mostrarSlide(indiceAtual);
}

// 🧠 Detecta se é mobile ou desktop
function verificaTamanho() {
    return window.innerWidth <= 768 ? 'mobile' : 'desktop';
}

let tipoAtual = verificaTamanho();

if (tipoAtual === 'mobile') {
    iniciarCarrossel('.carrossel-mobile');
} else {
    iniciarCarrossel('.carrossel');
}

// 🔄 Observa se há mudança de tamanho (quebra entre mobile e desktop)
window.addEventListener('resize', () => {
    const novoTipo = verificaTamanho();
    if (novoTipo !== tipoAtual) {
        tipoAtual = novoTipo;
        // Reinicia o carrossel correto
        if (novoTipo === 'mobile') {
            iniciarCarrossel('.carrossel-mobile');
        } else {
            iniciarCarrossel('.carrossel');
        }
    }
});



const btnMenu = document.querySelector('.menu-mobile');
const menu = document.querySelector('.menu');

btnMenu.addEventListener('click', () => {
  menu.classList.toggle('active');

  if (menu.classList.contains('active')) {
    btnMenu.textContent = '×'; // muda para X
    btnMenu.setAttribute('aria-label', 'Fechar menu');
    btnMenu.classList.add('rotate'); // gira o botão
  } else {
    btnMenu.textContent = '☰'; // volta para hambúrguer
    btnMenu.setAttribute('aria-label', 'Abrir menu');
    btnMenu.classList.remove('rotate'); // volta à posição normal
  }
});

// Slider Assistência
  

const galleries = document.querySelectorAll('.galery');

galleries.forEach(galery => {
  const images = galery.querySelectorAll('img');
  let index = 0;

  setInterval(() => {
    images[index].classList.remove('active');
    index = (index + 1) % images.length;
    images[index].classList.add('active');
  }, 2000);
});





const swiper = new Swiper(".mySwiper", {
  slidesPerView: 3.5, // valor para mobile
  spaceBetween: 15,
  breakpoints: {
    0: {
      slidesPerView: 3.5,
      navigation: false
    },
    768: {
      slidesPerView: 6,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev"
      }
    }
  }
});




(function() {
  const carrossel = document.querySelector('.carrossel-parceiros');
  const btnEsquerda = document.querySelector('.parceiros-seta.esquerda');
  const btnDireita = document.querySelector('.parceiros-seta.direita');

  if (!carrossel || !btnEsquerda || !btnDireita) return;

  const passo = 200;

  btnEsquerda.addEventListener('click', () => {
    carrossel.scrollBy({ left: -passo, behavior: 'smooth' });
  });

  btnDireita.addEventListener('click', () => {
    carrossel.scrollBy({ left: passo, behavior: 'smooth' });
  });

  // autoplay (opcional)
  setInterval(() => {
    carrossel.scrollBy({ left: passo, behavior: 'smooth' });
  }, 4000);
})();





 

