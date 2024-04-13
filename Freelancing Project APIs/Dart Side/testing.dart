


import "ImageHandler.dart";

void main() async {


  final imageHandlerObject = await ImageHandler.fromFile("C:/Users/Tareq/Desktop/12.bmp");
  // final imageHandlerObject = await ImageHandler.fromURL("https://tareqmahmood-001-site1.etempurl.com/freelancing%20project%20assets/profile%20pictures/1.jpg");

  // final imageHandlerObject = ImageHandler();


  print("Image width: ${imageHandlerObject.imageWidth}\n");
  print("Image height: ${imageHandlerObject.imageHeight}\n");
  print("Image number of pixels: ${imageHandlerObject.imageNumberOfPixels}\n");
  print("Image mime type: ${imageHandlerObject.getImageMimeType()}\n");
  print("Image size in bytes: ${imageHandlerObject.imageSizeInBytes}\n");

  print("Compressed image width: ${imageHandlerObject.compressedImageWidth}\n");
  print("Compressed image height: ${imageHandlerObject.compressedImageHeight}\n");
  print("Compressed image number of pixels: ${imageHandlerObject.compressedImageNumberOfPixels}\n");
  print("Compressed image mime type: ${imageHandlerObject.getCompressedImageMimeType()}\n");
  print("Compressed image size in bytes: ${imageHandlerObject.compressedImageSizeInBytes}\n");

  // imageHandlerObject.saveCompressedImageAsFile("C:/Users/Tareq/Desktop/tareq.jpg");
  
}
