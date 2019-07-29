UPGRADE
=======

## Upgrade FROM 0.4.0 to 0.5.0

 * Autoloading is removed, Questioners and Generators are now loaded
   from a conventional namespace (`Dance\Questioner` and `Dance\Generator` respectively);
   
 * Local dances are now automatically loaded, use `--no-local` to disable this behaviour;
 
 * Local dances no longer must end with `.dancer`; 
 
 * Update dances to the last version to fix "corrupted" dances.
