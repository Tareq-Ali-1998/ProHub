
import 'dart:io';
import 'dart:convert';
import 'package:collection/collection.dart';

class FileHandler {
  late File file;
  // I need to set the following attribute only once when setting the file and to use it in almost all the methods.
  late List<int> fileBytes;
  late String fileType;
/*
  String get fileType => _fileType;

  set fileType(String fileType) {
    _fileType = fileType;
  }

  List<int> get bytes => _bytes;

  set bytes(List<int> bytes) {
    _bytes = bytes;
    _fileType = _detectFileType();
  }

  FileType operator =(FileType other) {
    _bytes = other._bytes;
    _fileType = other._fileType;
    return this;
  }
*/

  // I can remember what I should do.

  bool isPDF() {
    final bytes = file.readAsBytesSync();
    if (bytes.length < 6) return false; // Make sure the file is at least 6 bytes long
    final signature = this.fileBytes.sublist(0, 10); // The CR2 signature is the first 10 bytes
    final eof = bytes.sublist(bytes.length - 6); // The PDF end-of-file marker is the last 6 bytes
    if (utf8.decode(signature) == '%PDF-' && utf8.decode(eof) == '%%EOF') {
      this.fileType = '%PDF-'; // Store the PDF file signature
      return true;
    } else {
      return false;
    }
  }


  bool isJPEG() {
    final bytes = file.readAsBytesSync();
    if (bytes.length < 2) return false; // Make sure the file is at least 2 bytes long
    final signature = this.fileBytes.sublist(0, 10); // The CR2 signature is the first 10 bytes
    if (signature[0] == 0xFF && signature[1] == 0xD8) {
      this.fileType = 'FFD8'; // Store the JPEG file signature as a hex string
      return true;
    } else {
      return false;
    }
  }


  bool isJPG() {
    final bytes = file.readAsBytesSync();
    if (bytes.length < 3) return false; // Make sure the file is at least 3 bytes long
    final signature = this.fileBytes.sublist(0, 10); // The CR2 signature is the first 10 bytes
    if (ListEquality().equals(signature, [0xFF, 0xD8, 0xFF])) {
      this.fileType = 'FFD8FF'; // Store the JPG file signature as a hex string
      return true;
    } else {
      return false;
    }
  }


  bool isPNG() {
    final bytes = file.readAsBytesSync();
    if (bytes.length < 8) return false; // Make sure the file is at least 8 bytes long
    final signature = this.fileBytes.sublist(0, 10); // The CR2 signature is the first 10 bytes
    if (ListEquality().equals(signature, [0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A])) {
      this.fileType = 'PNG'; // Store the PNG image signature as a string
      return true;
    } else {
      return false;
    }
  }


  bool isBMP() {
    final bytes = file.readAsBytesSync();
    if (bytes.length < 2) return false; // Make sure the file is at least 2 bytes long
    final signature = this.fileBytes.sublist(0, 10); // The CR2 signature is the first 10 bytes
    if (ListEquality().equals(signature, [0x42, 0x4D])) {
      this.fileType = 'BMP'; // Store the BMP image signature as a string
      return true;
    } else {
      return false;
    }
  }


  bool isGIF() {
    final bytes = file.readAsBytesSync();
    if (bytes.length < 6) return false; // Make sure the file is at least 6 bytes long
    final signature = this.fileBytes.sublist(0, 10); // The CR2 signature is the first 10 bytes
    if (utf8.decode(signature) == 'GIF89a' || utf8.decode(signature) == 'GIF87a') {
      this.fileType = 'GIF'; // Store the GIF image signature as a string
      return true;
    } else {
      return false;
    }
  }


  bool isJP2() {
    final bytes = file.readAsBytesSync();
    if (bytes.length < 12) return false; // Make sure the file is at least 12 bytes long
    final signature = this.fileBytes.sublist(0, 10); // The CR2 signature is the first 10 bytes
    if (ListEquality().equals(signature, [0x00, 0x00, 0x00, 0x0C, 0x6A, 0x50, 0x20, 0x20, 0x0D, 0x0A, 0x87, 0x0A])) {
      this.fileType = 'JP2'; // Store the JP2 image signature as a string
      return true;
    } else {
      return false;
    }
  }


  bool isTIFF() {
    final bytes = file.readAsBytesSync();
    if (bytes.length < 4) return false; // Make sure the file is at least 4 bytes long
    final signatureBE = bytes.sublist(0, 4); // The TIFF signature with big-endian byte order
    final signatureLE = bytes.sublist(0, 4).reversed.toList(); // The TIFF signature with little-endian byte order
    if (ListEquality().equals(signatureBE, [0x49, 0x49, 0x2A, 0x00]) || ListEquality().equals(signatureLE, [0x49, 0x49, 0x2A, 0x00]) || ListEquality().equals(signatureBE, [0x4D, 0x4D, 0x00, 0x2A]) || ListEquality().equals(signatureLE, [0x4D, 0x4D, 0x00, 0x2A])) {
      this.fileType = 'TIFF'; // Store the TIFF image signature as a string
      return true;
    } else {
      return false;
    }
  }
  

  bool isWebP() {
    final bytes = file.readAsBytesSync();
    if (bytes.length < 12) return false; // Make sure the file is at least 12 bytes long
    final signature = this.fileBytes.sublist(0, 10); // The CR2 signature is the first 10 bytes
    if (ListEquality().equals(signature.sublist(0, 4), [0x52, 0x49, 0x46, 0x46]) && 
        ListEquality().equals(signature.sublist(8, 12), [0x57, 0x45, 0x42, 0x50])) {
      this.fileType = 'WebP'; // Store the WebP image signature as a string
      return true;
    } else {
      return false;
    }
  }


  bool isHEIF() {
    final bytes = file.readAsBytesSync();
    if (bytes.length < 12) return false; // Make sure the file is at least 12 bytes long
    final signature = this.fileBytes.sublist(0, 10); // The CR2 signature is the first 10 bytes
    if (ListEquality().equals(signature, [0x00, 0x00, 0x00, 0x0C, 0x66, 0x74, 0x79, 0x70, 0x68, 0x65, 0x69, 0x63])) {
      this.fileType = 'HEIF'; // Store the HEIF image signature as a string
      return true;
    } else {
      return false;
    }
  }


  bool isAVIF() {
    final bytes = file.readAsBytesSync();
    if (bytes.length < 12) return false; // Make sure the file is at least 12 bytes long
    final signature = this.fileBytes.sublist(0, 10); // The CR2 signature is the first 10 bytes
    if (Utf8Decoder().convert(signature) == 'ftypavif') {
      this.fileType = 'AVIF'; // Store the AVIF image signature as a string
      return true;
    } else {
      return false;
    }
  }


  bool isCR2() {

    if (this.fileBytes.length < 10) return false; // Make sure the file is at least 10 bytes long
    final signature = this.fileBytes.sublist(0, 10); // The CR2 signature is the first 10 bytes
    if (ListEquality().equals(signature, [0x49, 0x49, 0x2A, 0x00, 0x10, 0x00, 0x00, 0x00, 0x43, 0x52])) {
      this.fileType = 'CR2'; // Store the CR2 image signature as a string
      return true;
    } else {
      return false;
    }
  }


  bool isMP3() {
    if (this.fileBytes.length < 3) return false;
    return ListEquality().equals(this.fileBytes.sublist(0, 3), [0xFF, 0xFB, 0x90]);
  }

  bool isMP4() {
    if (this.fileBytes.length < 12) return false;
    return ListEquality().equals(this.fileBytes.sublist(4, 8), [0x66, 0x74, 0x79, 0x70]);
  }
  

/*
  String _detectFileType() {
    final signature = _bytes.sublist(0, 12);
    if (ListEquality().equals(signature.sublist(0, 4), [0x52, 0x49, 0x46, 0x46]) &&
        ListEquality().equals(signature.sublist(8, 12), [0x57, 0x45, 0x42, 0x50])) {
      return 'WebP';
    } else if (ListEquality().equals(signature.sublist(0, 4), [0x25, 0x50, 0x44, 0x46])) {
      return 'PDF';
    } else if (ListEquality().equals(signature.sublist(0, 3), [0xFF, 0xFB, 0x90])) {
      return 'MP3';
    } else if (ListEquality().equals(signature.sublist(4, 8), [0x66, 0x74, 0x79, 0x70])) {
      return 'MP4';
    } else if (ListEquality().equals(signature.sublist(0, 4), [0x4F, 0x67, 0x67, 0x53])) {
      return 'OGG';
    } else if (ListEquality().equals(signature.sublist(0, 8), [0x4F, 0x70, 0x75, 0x73, 0x48, 0x65, 0x61, 0x64])) {
      return 'OPUS';
    } else if (ListEquality().equals(signature.sublist(0, 4), [0x52, 0x49, 0x46, 0x46]) &&
               ListEquality().equals(signature.sublist(8, 12), [0x57, 0x41, 0x56, 0x45])) {
      return 'WAV';
    } else if (ListEquality().equals(signature.sublist(4, 8), [0x66, 0x74, 0x79, 0x70]) &&
               ListEquality().equals(signature.sublist(12, 16), [0x4D, 0x34, 0x41, 0x20])) {
      return 'M4A';
    } else {
      return 'Unknown';
    }
  }



  bool isPDF() {
    final bytes = _bytes;
    if (bytes.length < 5) return false; // Make sure the file is at least 5 bytes long
    
    // Check if the file starts with the PDF signature
    if (!ListEquality().equals(bytes.sublist(0, 4), [0x25, 0x50, 0x44, 0x46])) {
      return false;
    }
    
    // Check if the file contains the "%%EOF" marker at the end
    final eofIndex = bytes.lastIndexOf([0x25, 0x25, 0x45, 0x4F, 0x46]); // Search for the "%%EOF" marker
    if (eofIndex == -1) return false; // Marker not found
    if (eofIndex + 5 != bytes.length) return false; // Marker not at the end of the file
    
    // Check if the file contains the "startxref" keyword before the "%%EOF" marker
    final startxrefIndex = bytes.lastIndexOf([0x73, 0x74, 0x61, 0x72, 0x74, 0x78, 0x72, 0x65, 0x66], eofIndex - 10); // Search for the "startxref" keyword
    if (startxrefIndex == -1) return false; // Keyword not found or not before the "%%EOF" marker
    
    // Check if the file contains the "%%EOF" keyword after the "startxref" keyword
    final eofIndex2 = bytes.indexOf([0x25, 0x25, 0x45, 0x4F, 0x46], startxrefIndex); // Search for the "%%EOF" marker after the "startxref" keyword
    if (eofIndex2 == -1 || eofIndex2 >= eofIndex) return false; // Keyword not found or found after the "%%EOF" marker
    
    return true;
  }
*/

  bool isAudioFile() {
    final fileType = this.fileType;
    return fileType == 'WAV' ||
           fileType == 'MP3' ||
           fileType == 'M4A' ||
           fileType == 'OGG' ||
           fileType == 'OPUS';
  }

  
}