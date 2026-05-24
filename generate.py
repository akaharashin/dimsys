
import pandas as pd
import json
# Generate kas kecil
df_kas = pd.read_excel('storage/app/_PUSAT MEI 2026.xlsx', sheet_name='KAS KECIL', header=None)
df_kas_data = df_kas.iloc[2:, :8].copy()
df_kas_data.columns = ['tanggal', 'kategori', 'sub_kategori', 'rekening', 'tanggal_penjualan', 'x', 'debit', 'kredit']
df_kas_data = df_kas_data[df_kas_data['tanggal'].notna() & df_kas_data['kategori'].notna()]
df_kas_data['debit'] = pd.to_numeric(df_kas_data['debit'], errors='coerce').fillna(0)
df_kas_data['kredit'] = pd.to_numeric(df_kas_data['kredit'], errors='coerce').fillna(0)
df_kas_data = df_kas_data[(df_kas_data['debit'] > 0) | (df_kas_data['kredit'] > 0)]
df_kas_data['tanggal_str'] = df_kas_data['tanggal'].apply(lambda x: str(x)[:10])

kas_result = []
for _, row in df_kas_data.iterrows():
    kas_result.append({
        'tanggal': row['tanggal_str'],
        'kategori': str(row['kategori']).strip() if pd.notna(row['kategori']) else '',
        'sub_kategori': str(row['sub_kategori']).strip() if pd.notna(row['sub_kategori']) else '',
        'penerima': str(row['rekening']).strip() if pd.notna(row['rekening']) else '',
        'tipe': 'debit' if row['debit'] > 0 else 'kredit',
        'jumlah': int(row['debit']) if row['debit'] > 0 else int(row['kredit']),
    })

with open('storage/app/kas_kecil_mei.json', 'w') as f:
    json.dump(kas_result, f)

print(f'Kas Kecil: {len(kas_result)} transaksi tersimpan')