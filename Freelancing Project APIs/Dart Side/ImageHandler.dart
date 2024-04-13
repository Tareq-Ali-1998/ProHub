import "dart:io";
import "dart:convert";
import "dart:typed_data";
import "package:image/image.dart";
import "package:mime/mime.dart";

// TODO: I have to deal with the error of calling the default constructor inside any other constuctor and study that case carefully

class ImageHandler {
  late Image _image;
  late Uint8List _imageByteList;
  late String _imageMimeType;
  late Image _compressedImage;
  late Uint8List _compressedImageByteList;
  late String _compressedImageMimeType;
  /*
   * Supported image types, and here it's not recommended to add more types but you can delete some
   * types for sure, this is because the current images Dart library version "image 4.0.17" supports
   * both reading and writing only for the following provided types.
   */
  static const List<String> supportedImageTypes = [
    "image/jpeg",
    "image/png",
    "image/gif",
    "image/tiff",
    "image/bmp",//
    "image/x-tga",//
    "image/x-icon"//
  ];




  // Getters for the image
  Image get image => this._image;
  Uint8List get imageByteList => this._imageByteList;
  int get imageWidth => this._image.width;
  int get imageHeight => this._image.height;
  int get imageNumberOfPixels => this._image.width * this._image.height;
  int get imageSizeInBytes => this._imageByteList.length;

  // Getters for the compressed image
  Image get compressedImage => this._compressedImage;
  Uint8List get compressedImageByteList => this._compressedImageByteList;
  int get compressedImageWidth => this._compressedImage.width;
  int get compressedImageHeight => this._compressedImage.height;
  int get compressedImageNumberOfPixels => this._compressedImage.width * this._compressedImage.height;
  int get compressedImageSizeInBytes => this._compressedImageByteList.length;



  // Constructor for PNG image
  ImageHandler() {
    this._image = Image(width: 0, height: 0);
    this._imageByteList = Uint8List.fromList([0xFF, 0xD8]);
    this._imageMimeType = this.getImageMimeType();
    this._compressedImage = this._image;
    this._compressedImageByteList = this._imageByteList;
    this._compressedImageMimeType = this._imageMimeType;
  }

  // // The default constructor
  // ImageHandler() {
  //   // Create a default image with 0 * 0 pixels and JPEG mime type
  //   this._image = Image(width: 0, height: 0);
  //   this._imageByteList = Uint8List.fromList([0xFF, 0xD8]);
  //   // And here the image type should be JPEG
  //   this._imageMimeType = this.getImageMimeType();
  //   this._compressedImage = this._image;
  //   this._compressedImageByteList = this._imageByteList;
  //   this._compressedImageMimeType = this._imageMimeType;
  // }

  ImageHandler.fromImage(Image? image) {
    // Here in this constructor, the compressed image is just a copy of the main image
    if ((image == null ) || (!image.isValid)) {
      ImageHandler();
      return;
    }
    
    this._imageByteList = image.buffer.asUint8List();
    if (!this.isSupportedImage()) {
      ImageHandler();
      return;
    }

    this._image = image;
    this._imageMimeType = this.getImageMimeType();
    this._compressedImage = this._image;
    this._compressedImageByteList = this._imageByteList;
    this._compressedImageMimeType = this._imageMimeType;
  }

  // Method to read an image from a file path
  ImageHandler.fromFile(String filePath) {
    _fromFile(filePath);
  }

  Future<void> _fromFile(String filePath) async {
    try {
      
      // Read the image from file asynchronously
      this._imageByteList = await File(filePath).readAsBytesSync();
      this._imageMimeType = this.getImageMimeType();

      if (!this.isSupportedImage()) {
        ImageHandler();
        return;
      }

      // Decode the image from bytes
      final Image? currentImage = decodeImage(this._imageByteList);

      // Check if the image is valid
      if ((currentImage == null) || (!currentImage.isValid)) {
        ImageHandler();
        return;
      }

      // Update the attributes
      this._image = currentImage;
      this._compressedImage = this._image;
      this._compressedImageByteList = this._imageByteList;
      this._compressedImageMimeType = this._imageMimeType;
    }
    catch (exception) {
      ImageHandler();
      return;
    }
  }

  // Method to read an image from a URL
  ImageHandler.fromURL(String url) {
    _fromURL(url);
  }

  Future<void> _fromURL(String url) async {
    try {
      final HttpClient httpClient = HttpClient();
      final HttpClientRequest request = await httpClient.getUrl(Uri.parse(url));
      final HttpClientResponse response = await request.close();
      final List<List<int>> responseBytes = await response.toList();
      final Uint8List imageBytes = Uint8List.fromList(responseBytes.expand((list) => list).toList());
      final Image? currentImage = decodeImage(imageBytes);

      if (currentImage == null || (!currentImage.isValid)) {
        ImageHandler();
        return;
      }

      // Update the attributes
      this._imageByteList = imageBytes;
      this._imageMimeType = this.getImageMimeType();
      if (!this.isSupportedImage()) {
        ImageHandler();
        return;
      }
      this._image = currentImage;
      this._compressedImage = this._image;
      this._compressedImageByteList = this._imageByteList;
      this._compressedImageMimeType = this._imageMimeType;
    } 
    catch (exception) {
      ImageHandler();
      return;
    }
  }






  String getImageMimeType() {
    final String? imageMimeType = lookupMimeType('', headerBytes: this._imageByteList);
    if (imageMimeType != null && supportedImageTypes.contains(imageMimeType)) {
      return imageMimeType;
    }
    else {
      return "unsupported image format";
    }
  }

  String getCompressedImageMimeType() {
    final String? compressedImageMimeType = lookupMimeType('', headerBytes: this._compressedImageByteList);
    if (compressedImageMimeType != null && supportedImageTypes.contains(compressedImageMimeType)) {
      return compressedImageMimeType;
    }
    else {
      return "unsupported image format";
    }
  }

  // Method to check if the current image type is valid
  bool isSupportedImage() {
    return _imageMimeType.startsWith("image/");
  }

  // Method to check if the current compressed image type is valid
  bool isSupportedCompressedImage() {
    return _compressedImageMimeType.startsWith("image/");
  }






  bool saveImageAsFile(String filePath) {
    try {
      File(filePath).writeAsBytesSync(this._imageByteList);
      return true;
    }
    catch (exception) {
      return false;
    }
  }

  bool saveCompressedImageAsFile(String filePath) {
    try {
      File(filePath).writeAsBytesSync(this._compressedImageByteList);
      return true;
    }
    catch (exception) {
      return false;
    }
  }







  bool convertToJPEG() {
    final Image? convertedImage = decodeJpg(this._imageByteList);
    if ((convertedImage == null) || (!convertedImage.isValid)) {
      return false;
    }
    this._imageByteList = convertedImage.buffer.asUint8List();
    this._imageMimeType = this.getImageMimeType();
    if (!this.isSupportedImage()) {
      this._imageByteList = this._image.buffer.asUint8List();;
      this._imageMimeType = this.getImageMimeType();
      return false;
    }

    this._image = convertedImage;
    this._compressedImageByteList = convertedImage.buffer.asUint8List();
    this._compressedImage = convertedImage;
    this._compressedImageMimeType = this._imageMimeType;
    return true;
  }

  bool convertToPNG() {
      final Image? convertedImage = decodePng(this._imageByteList);
      if ((convertedImage == null) || (!convertedImage.isValid)) {
        return false;
      }
      this._imageByteList = convertedImage.buffer.asUint8List();
      this._imageMimeType = this.getImageMimeType();
      if (!this.isSupportedImage()) {
        this._imageByteList = this._image.buffer.asUint8List();
        this._imageMimeType = this.getImageMimeType();
        return false;
      }

      this._image = convertedImage;
      this._compressedImageByteList = convertedImage.buffer.asUint8List();
      this._compressedImage = convertedImage;
      this._compressedImageMimeType = this._imageMimeType;
      return true;
    }

  bool convertToGIF() {
    final Image? convertedImage = decodeGif(this._imageByteList);
    if ((convertedImage == null) || (!convertedImage.isValid)) {
      return false;
    }
    this._image = convertedImage;
    this._imageByteList = convertedImage.buffer.asUint8List();
    this._imageMimeType = this.getImageMimeType();
    this._compressedImage = convertedImage;
    this._compressedImageByteList = convertedImage.buffer.asUint8List();
    this._compressedImageMimeType = this.getImageMimeType();
    return true;
  }

  bool convertToBMP() {
    final Image? convertedImage = decodeBmp(this._imageByteList);
    if ((convertedImage == null) || (!convertedImage.isValid)) {
      return false;
    }
    this._image = convertedImage;
    this._imageByteList = convertedImage.buffer.asUint8List();
    this._imageMimeType = this.getImageMimeType();
    this._compressedImage = convertedImage;
    this._compressedImageByteList = convertedImage.buffer.asUint8List();
    this._compressedImageMimeType = this.getImageMimeType();
    return true;
  }

  bool convertToTIFF() {
  final Image? convertedImage = decodeTiff(this._imageByteList);
  if ((convertedImage == null) || (!convertedImage.isValid)) {
    return false;
  }
  this._imageByteList = convertedImage.buffer.asUint8List();
  this._imageMimeType = this.getImageMimeType();
  if (!this.isSupportedImage()) {
    this._imageByteList = this._image.buffer.asUint8List();
    this._imageMimeType = this.getImageMimeType();
    return false;
  }

  this._image = convertedImage;
  this._compressedImageByteList = convertedImage.buffer.asUint8List();
  this._compressedImage = convertedImage;
  this._compressedImageMimeType = this.getImageMimeType();
  return true;
}

  bool convertToTGA() {
    final Image? convertedImage = decodeTga(this._imageByteList);
    if ((convertedImage == null) || (!convertedImage.isValid)) {
      return false;
    }
    this._imageByteList = convertedImage.buffer.asUint8List();
    this._imageMimeType = this.getImageMimeType();
    if (!this.isSupportedImage()) {
      this._imageByteList = this._image.buffer.asUint8List();
      this._imageMimeType = this.getImageMimeType();
      return false;
    }

    this._image = convertedImage;
    this._compressedImageByteList = convertedImage.buffer.asUint8List();
    this._compressedImage = convertedImage;
    this._compressedImageMimeType = this.getImageMimeType();
    return true;
  }

  bool convertToICO() {
    final Image? convertedImage = decodeIco(this._imageByteList);
    if ((convertedImage == null) || (!convertedImage.isValid)) {
      return false;
    }
    this._imageByteList = convertedImage.buffer.asUint8List();
    this._imageMimeType = this.getImageMimeType();
    if (!this.isSupportedImage()) {
      this._imageByteList = this._image.buffer.asUint8List();
      this._imageMimeType = this.getImageMimeType();
      return false;
    }

    this._image = convertedImage;
    this._compressedImageByteList = convertedImage.buffer.asUint8List();
    this._compressedImage = convertedImage;
    this._compressedImageMimeType = this.getImageMimeType();
    return true;
  }




  // Method to get the base64-encoded image
  String getBase64EncodedImage() {
    return base64.encode(this._imageByteList);
  }

  // Method to get the base64-encoded compressed image
  String getBase64EncodedCompressedImage() {
    return base64.encode(this._compressedImageByteList);
  }



}