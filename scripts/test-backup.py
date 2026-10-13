"""Teste isolado do empacotamento; não acessa Docker nem dados reais."""
import io
import os
from pathlib import Path
import subprocess
import tarfile
import tempfile

script = Path(__file__).resolve().with_name('backup.sh')
with tempfile.TemporaryDirectory() as directory:
    root = Path(directory)
    docker = root / 'docker'
    docker.write_text('''#!/usr/bin/env python3
import io, os, sys, tarfile
if os.environ.get('FAIL_BACKUP') == '1':
    sys.exit(1)
if 'mysql' in sys.argv:
    sys.stdout.write('CREATE TABLE backup_test (id INT);')
else:
    with tarfile.open(fileobj=sys.stdout.buffer, mode='w|gz') as archive:
        content = b'foto de teste'
        entry = tarfile.TarInfo('photo.txt')
        entry.size = len(content)
        archive.addfile(entry, io.BytesIO(content))
''')
    docker.chmod(0o700)
    env = dict(os.environ, PATH=f'{root}:'+os.environ['PATH'], BACKUP_PASSPHRASE='somente-teste-local', BACKUP_DIRECTORY=str(root / 'backups'))
    subprocess.run(['bash', str(script)], env=env, check=True, capture_output=True)
    files = list((root / 'backups').glob('*.enc'))
    assert len(files) == 1
    restored = subprocess.run(['openssl', 'enc', '-d', '-aes-256-cbc', '-pbkdf2', '-iter', '200000', '-in', str(files[0]), '-pass', 'env:BACKUP_PASSPHRASE'], env=env, check=True, capture_output=True).stdout
    with tarfile.open(fileobj=io.BytesIO(restored), mode='r:gz') as archive:
        assert b'CREATE TABLE' in archive.extractfile('database.sql').read()
        photos = archive.extractfile('photos.tar.gz').read()
    with tarfile.open(fileobj=io.BytesIO(photos), mode='r:gz') as archive:
        assert archive.extractfile('photo.txt').read() == b'foto de teste'
    env['FAIL_BACKUP'] = '1'
    assert subprocess.run(['bash', str(script)], env=env, capture_output=True).returncode != 0
    assert len(list((root / 'backups').glob('*.enc'))) == 1
print('Backup: criptografia, recuperação do conteúdo e falha sem falso sucesso verificados.')
