kreactive
=========

L'api expose les méthodes suivantes :

format de retour: JSON

    - Creation d'un utilisateur
         chemin : /user
         méthode : POST
         Données obligatoires : pseudo (string), email (email format)  
        
    - Creation d'un choix
         chemin : /choice
         méthode : POST
         Données obligatoires : user (User), film (Film)  
    
    - Suppression d'un choix
         chemin : /choice/{id}
         méthode : DELETE
         paramètres obligatoires : id (int)  
    
    - Liste des films pour un utilisateur
        chemin : /choicesByUser/{id}
        méthode : GET
        paramètres obligatoires : id (int)  
    
    - Liste des utilisateurs pour un film
        chemin : /usersByFilm/{id}
        méthode : GET
        paramètres obligatoires : id (int)  

    - liste le meilleur film choisi
        chemin : /bestFilm/
        méthode : GET