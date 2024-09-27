<?php if(isset($_GET['action']) && $_GET['action'] === "added"): ?>
    <div class="alert alert-success alert-dismissible fade show" id="fadeaway">            
        <strong>Erfolg!</strong> Eintrag wurde erfolgreich hinzugefügt.                    
    </div>
<?php endif; ?>  

<?php if(isset($_GET['action']) && $_GET['action'] === "error"): ?>
    <div class="alert alert-danger alert-dismissible fade show" id="fadeaway">
        <strong>Error!</strong> Die Eingabe war fehlerhaft.        
    </div>
<?php endif; ?>    

<?php if(isset($_GET['action']) && $_GET['action'] === "deleted"): ?>
    <div class="alert alert-warning alert-dismissible fade show" id="fadeaway">            
        <strong>Erfolg!</strong> Eintrag wurde erfolgreich gelöscht.                    
    </div>
<?php endif; ?> 

<script>

$("#fadeaway").fadeOut(4500, 'swing');

</script>