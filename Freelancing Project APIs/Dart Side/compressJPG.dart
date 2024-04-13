import 'dart:io';
import 'package:image/image.dart';

void convertToJpeg(String inputFilePath, String outputFilePath) {
  final inputFile = File(inputFilePath);
  final bytes = inputFile.readAsBytesSync();
  final image = decodeImage(bytes);

  if (image != null) {
    // Convert the image to the JPEG format
    final jpeg = encodeJpg(image, quality: 85);

    final outputFile = File(outputFilePath);
    outputFile.writeAsBytesSync(jpeg);
  } else {
    print('Error: Could not decode image from $inputFilePath');
  }
}


void main() {
  
  // Take care of the file extension.
  var inputPath = "C:/Users/Tareq/Desktop/lol.jpg";
  String outputPath = "C:/Users/Tareq/Desktop/New Lol.jpg";

  convertToJpeg(inputPath, outputPath);

}