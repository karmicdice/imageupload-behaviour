# Image Upload Behavior

Usage:

In the desired table, create a column called 'image' (can be changed, read on)

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->addBehavior('Imagewebpupload');
    }

Copy the `APP/Model/Behavior/ImagawebpuploadBehavior.php` in `APP/Model/Behavior/`

Create a folder `uploads` under `WEBROOT/img`
and change the ownership or give write permissions.

```
chown -R www-data:www-data uploads
chmod a+w uploads
```

Done!


### Changing Database Field name

For example, if your field is called `avatar`.

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->addBehavior('Imagewebpupload', 
                                ['field' => 'avatar'] );
    }
    
    
### Changing Upload path from img/uploads to something else.

You can extend it by making use of the setter functions.