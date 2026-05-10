import multer from 'multer';
import path from 'path';

const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    const __dirname = path.resolve(path.dirname(''));
    cb(null, path.resolve(__dirname, 'uploads'));
  },
  filename: function (req, file, cb) {
    const filename = `wppConnect-${Date.now()}-${file.originalname}`;
    cb(null, filename);
  },
});

const uploads = multer({ storage: storage });
export default uploads;
