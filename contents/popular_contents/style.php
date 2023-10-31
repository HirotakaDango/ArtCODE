    <style>
      @media (min-width: 768px) {
        .d-desktop {
          display: block;
        }

        .d-mobile {
          display: none;
        }

        .carousel-caption {
          border-radius: 0 0 5px 5px;
        }
      }

      @media (max-width: 767px) {
        .h-custom {
          height: 225px;
          object-fit: cover;
          object-position: top;
        }

        .d-desktop {
          display: none;
        }

        .d-mobile {
          display: block;
        }
      }

      .grid-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        grid-gap: 3px;
        justify-items: center;
        align-items: center;
        margin: 0 4px;
      }

      .grid-item {
        position: relative;
        width: 100%;
        object-fit: cover;
        object-position: top;
      }

      .carousel-caption {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 10px;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);
        text-align: center;
      }
      
      .w-99 {
        width: 98.45%;
      }
    
      .item {
        display: flex;
        grid-row: span 2;
        height: 100%;
      }


      .item:first-child {
        grid-column: span 2;
        grid-row: span 2;
        height: 100%;
      }

      .item img {
        width: 100%;
        height: 300px;
        object-fit: cover;
        object-position: top;
      }

      .carousel-caption h5 {
        font-size: 20px;
        margin: 0;
        padding: 5px;
      }
      
      .rotating-class {
        animation: loader 2s infinite; /* Reducing animation duration for more FPS */
        display: flex;
        justify-content: center;
        align-items: center;
        will-change: transform;
        transform-origin: center;
      }

      @keyframes loader {
        0% {
          transform: rotate(0deg);
        }
        100% {
          border-radius: 50%;
          transform: rotate(360deg); /* Use 360deg for a full rotation */
        }
      }

      .rotating-class {
        animation-timing-function: steps(60); /* You can experiment with the number of steps */
      }

      .hori {
        border-radius: 5px;
        width: 100px;
        height: 120px;
        object-fit: cover;
      }

      .media-scroller {
        display: grid;
        gap: 4px; /* Updated gap value */
        grid-auto-flow: column;
        overflow-x: auto;
        overscroll-behavior-inline: contain;
      }

      .snaps-inline {
        scroll-snap-type: inline mandatory;
        scroll-padding-inline: var(--_spacer, 1rem);
      }
  
      .snaps-inline > * {
        scroll-snap-align: start;
      }

      .scrollable-div {
        overflow: auto;
        scrollbar-width: thin;  /* For Firefox */
        -ms-overflow-style: none;  /* For Internet Explorer and Edge */
        scrollbar-color: transparent transparent;  /* For Chrome, Safari, and Opera */
      }

      .scrollable-div::-webkit-scrollbar {
        width: 0;
        height: 0;
        background-color: transparent;
      }
      
      .scrollable-div::-webkit-scrollbar-thumb {
        background-color: transparent;
      }
      
      .blurred {
        filter: blur(4px);
      }
    </style>