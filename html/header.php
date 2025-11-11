<style>
    .header_container {
        background-image: url(../img/textures/grass.jpg);
        background-repeat: repeat;
        width: 100%;
        min-height: 80px !important;
        max-height: 80px !important;
        height: 80px !important;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding: 10px;
        /* padding-top: 20px;
        padding-bottom: 20px; */
        margin: 0 !important;
        overflow: hidden;
        position: relative;
    }

    .header_logo {
        max-width: 300px;
        max-height: 60px;
        height: auto;
        width: auto;
        object-fit: contain;
        position: relative;
        z-index: 2;
    }
    
    @media (max-width: 768px) {
        .header_container {
            min-height: 60px;
            padding: 8px;
        }
        
        .header_logo {
            max-width: 250px;
            max-height: 50px;
        }
    }
    
    @media (max-width: 480px) {
        .header_container {
            display: none !important;
        }
    }
</style>

<div class="header_container">
    <img src="../img/header_logo_cropped.png" class="header_logo" alt="Fantasy Bundesliga Logo">
</div>