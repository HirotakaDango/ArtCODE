        </main> 
      </div>
    </div>
    <style>
      @media (max-width: 767px) {
        .navbar-brand {
          position: static;
          display: block;
          text-align: center;
          margin: auto;
          transform: none;
        }

        .navbar-brand {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          font-size: 18px;
        }
      }

      .navbar-brand {
        font-size: 18px;
      }
      
      @media (min-width: 992px) {
        .navbar-toggler1 {
          display: none;
        }
        
        .text-nav {
            text-align: left;
        }
      }
    
      .navbar-toggler1 {
        background-color: #ededed;
        border: none;
        font-size: 8px;
        margin-left: 8px;
        border-radius: 5px;
        padding: 6px;
        transition: background-color 0.3s ease; 
      }

      .navbar-toggler1:hover {
        background-color: rgba(0,0,0,0.2);
      }  
    </style>
    <?php include('bootstrapjs.php'); ?>
  </body>
</html>