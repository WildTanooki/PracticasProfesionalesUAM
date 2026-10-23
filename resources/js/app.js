import '../css/app.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

//carrusel

document.addEventListener('DOMContentLoaded', () => {
    const tarjetas = Array.from(
        document.querySelectorAll('[data-practica-slide]')
    );

    const anterior = document.getElementById('practica-anterior');
    const siguiente = document.getElementById('practica-siguiente');
    const numero = document.getElementById('numero-practica');

    const indicadores = Array.from(
        document.querySelectorAll('[data-practica-indicador]')
    );

    if (!tarjetas.length || !anterior || !siguiente || !numero) {
        return;
    }

    let indiceActual = 0;

    function mostrarPractica(indice) {
        indiceActual = (indice + tarjetas.length) % tarjetas.length;

        tarjetas.forEach((tarjeta, indice) => {
            tarjeta.hidden = indice !== indiceActual;
        });

        indicadores.forEach((indicador, indice) => {
            const activo = indice === indiceActual;

            indicador.setAttribute(
                'aria-current',
                activo ? 'true' : 'false'
            );

            indicador.classList.toggle('w-6', activo);
            indicador.classList.toggle('bg-white', activo);

            indicador.classList.toggle('w-2', !activo);
            indicador.classList.toggle('bg-white/50', !activo);
        });

        numero.textContent =
            `Práctica ${indiceActual + 1} de ${tarjetas.length}`;
    }

    anterior.addEventListener('click', () => {
        mostrarPractica(indiceActual - 1);
    });

    siguiente.addEventListener('click', () => {
        mostrarPractica(indiceActual + 1);
    });

    indicadores.forEach((indicador, indice) => {
        indicador.addEventListener('click', () => {
            mostrarPractica(indice);
        });
    });

    mostrarPractica(0);
});
