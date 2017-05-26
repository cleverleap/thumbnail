# PHP Thumbnail Generator

Whatever web site you are building, you may come to a need to generate picture thumbnails in various sizes and dimensions. PHP Thumbnail Generator is lightweight class that will help you generate thumbnails. It is easy to use, takes 3 setup parameters (width, height and optional crop option) and a path to picture for which you want to generate a thumbnail. 

## Usage

### Installing

Download repository into your local folder e.g. "lib".

If you are using Composer add mapping into your composer file:

`{
    "require": {
        ...
        },
    "autoload": {
        "psr-4": {
            "Cleverleap\\Thumbnail\\": "lib/thumbnail/src"
        }
    }
}`

Or just include "lib/thumbnail/thumbnail.php" in your controller.

### How to use

In your controller, add use statement:

`use Cleverleap\Thumbnail\Thumbnail;`

To generate picture and display it to the user, pick a method that will respond with a picture thumbnail. 

`
	public function getIndex($path) {
		
		$thumbnail = new Thumbnail(['cacheDir'=>'userdata/cacheimages/']);
		$thumbnail->setWidth( $this->request->get('w') )
				  ->setHeight( $this->request->get('h') );
				  
		if( $this->request->get('crop') == 1) {
			$thumbnail->crop(TRUE);
		}
		
		$thumbnail->make($path);
	}
`

## License

This project is licensed under the MIT License
